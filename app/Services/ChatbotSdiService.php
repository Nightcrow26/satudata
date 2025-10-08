<?php

namespace App\Services;

use App\Services\Nlp\IntentExtractor;
use App\Services\Data\DatasetSearchService;
use App\Services\Data\DataAnalyzer;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ChatbotSdiService
{
    public function __construct(
        private ?IntentExtractor $intent = null,
        private ?DatasetSearchService $search = null,
        private ?DataAnalyzer $analyzer = null,
        private ?Client $http = null,
    ) {
        $this->intent   = $this->intent   ?: new \App\Services\Nlp\IntentExtractor();
        $this->search   = $this->search   ?: new \App\Services\Data\DatasetSearchService();
        $this->analyzer = $this->analyzer ?: new \App\Services\Data\DataAnalyzer();
        $this->http     = $this->http     ?: new Client(['base_uri' => 'https://api.openai.com/v1/']);
    }

    private function readExcelPreview(string $pathLocal, int $maxRows = 40): array
    {
        $spreadsheet = IOFactory::load($pathLocal);
        $sheet = $spreadsheet->getSheet(0);
        $data  = $sheet->toArray(null, true, true, true); // indeks kolom A,B,C,...
        if (!$data) return ['columns'=>[], 'rows'=>[]];

        // Baris pertama sebagai header (fallback jika kosong)
        $headers = array_map(
            fn($v) => trim((string)$v) ?: 'COL_' . uniqid(),
            array_values($data[1] ?? [])
        );

        $rows = [];
        $n = 0;
        foreach ($data as $idx => $row) {
            if ($idx === 1) continue; // skip header
            $obj = [];
            $i = 0;
            foreach ($row as $cell) {
                $obj[$headers[$i] ?? ('COL_'.$i)] = is_string($cell) ? trim($cell) : $cell;
                $i++;
            }
            $rows[] = $obj;
            if (++$n >= $maxRows) break;
        }
        return ['columns'=>$headers, 'rows'=>$rows];
    }

    public function stream(string $message, array $context, \Closure $emit, \Closure $tick): void
    {
        // Create response tracker for this specific request
        $responseState = ['emitted' => false];
        
        // Log start of stream to help debug multiple calls
        \Log::info('ChatbotSdiService::stream started', [
            'message' => substr($message, 0, 50), 
            'instance_id' => spl_object_id($this),
            'request_id' => uniqid()
        ]);
        
        // 1) Intent
        $emit('delta', ['text' => "🔎 Memahami pertanyaan… "]);
        $intent = $this->safeExtractIntent($message);
        $years  = $intent['years'] ?? null; // <— tambahkan ini
        $tick();

        // 2) Search
        $emit('delta', ['text' => "\n📚 Mencari dataset terkait di situs… "]);
        $cands = $this->search->search($intent['keywords'] ?? [], $years['from'] ?? null, $years['to'] ?? null, $years['exact'] ?? null, 6);

        // Ambil 2–3 kandidat terbaik (hindari duplikat nama)
        $picks = collect($cands)
            ->unique('nama')
            ->take((int)env('CHATBOT_MAX_DATASETS', 3))
            ->values()
            ->all();

        if (empty($cands)) {
            // 3A) Tidak ketemu → jawab berbasis pengetahuan umum (+ beri tahu)
            $emit('delta', ['text' => "\n⚠️ Dataset belum tersedia di situs. Menyusun jawaban umum… "]);
            $final = $this->fallbackGeneralAnswer($message);
            $final['answer'] = "Catatan: dataset yang Anda minta belum tersedia di website ini.\n\n".$final['answer'];
            $this->emitFinalOnce($emit, $final, $responseState);
            return;
        }

        // 3B) Ketemu → analisis via JSON preview
        $best = $cands[0];
        $emit('delta', ['text' => "\n📥 Menyiapkan analisis untuk: ".implode(' • ', array_map(fn($d)=>$d['nama'], $picks))."…"]);
        try {
            $final = $this->analyzeMultipleDatasetsViaJson($picks, $message, $emit);
            $this->emitFinalOnce($emit, $final, $responseState);
            return;
        } catch (\Throwable $e) {
            $emit('delta', ['text' => "\n⚠️ Gagal analisis: ".substr($e->getMessage(),0,200).'…']);
            $fallback = $this->fallbackGeneralAnswer($message);
            $fallback['answer'] = "Catatan: dataset terdeteksi, namun analisis otomatis gagal.\n\n".$fallback['answer'];
            $this->emitFinalOnce($emit, $fallback, $responseState);
            return;
        }
    }

    private function safeExtractIntent(string $q): array
    {
        try {
            $raw = $this->intent->extract($q);
            if (is_string($raw)) return json_decode($raw, true) ?: ['keywords'=>[]];
            if (is_array($raw))  return $raw;
        } catch (\Throwable) {}
        // fallback heuristic minimal
        preg_match_all('/\d{4}/', $q, $ys);
        return [
            'keywords' => preg_split('/\s+/', trim($q)) ?: [],
            'years' => $ys[0] ? ['from'=>(int)min($ys[0]), 'to'=>(int)max($ys[0])] : null
        ];
    }

    private function fallbackGeneralAnswer(string $question): array
    {
        $payload = [
            'model' => env('OPENAI_MODEL','gpt-4o-mini'),
            'input' => [
                ['role'=>'system','content'=>
                    "Jawab ringkas dalam Bahasa Indonesia. Jika relevan, sarankan indikator/ sumber resmi umum terutama terkait Kabupaten Hulu Sungai Utara."],
                ['role'=>'user','content'=>$question]
            ],
            'stream' => false
        ];
        $resp = $this->http->post('responses', [
            'headers'=>[
                'Authorization'=>'Bearer '.env('OPENAI_API_KEY'),
                'Content-Type'=>'application/json'
            ],
            'json'=>$payload,
            'timeout'=>30
        ]);
        $data = json_decode($resp->getBody()->getContents(), true);
        $text = $data['output'][0]['content'][0]['text'] ?? 'Maaf, belum ada jawaban.';
        return ['answer' => $text, 'sources'=>[], 'data_preview'=>[], 'viz'=>null];
    }

    private function guessDisk(?string $path): string
    {
        if (!$path) return 'local';
        // heuristik: jika path bukan storage lokal -> s3
        if (str_starts_with($path, 's3://') || !str_starts_with($path, 'public/')) return 's3';
        return 'local';
    }

    /**
     * Analisis utama: dari Excel → preview JSON → Responses API (tanpa PDF)
     */
    public function analyzeMultipleDatasetsViaJson(array $datasets, string $question, \Closure $emit): array
    {
        if (empty($datasets)) {
            return ['answer'=>'Tidak ada dataset yang cocok.','insights'=>[],'viz'=>[],'data_preview'=>[],'sources'=>[]];
        }

        $emit('delta', ['text' => "\n🧩 Menyiapkan Jawaban…"]);

        $parts = [];    // potongan JSON per dataset
        $sources = [];
        $totalCells = 0;

        foreach ($datasets as $idx => $d) {
            $title = (string)($d['nama'] ?? ('Dataset #'.($idx+1)));
            $excel = $d['excel'] ?? null;
            if (!$excel) continue;

            $disk = $this->guessDisk($excel);

            // Siapkan path file lokal sementara
            $tmpXlsx = storage_path('app/tmp/'.Str::uuid().'.xlsx');
            if (!is_dir(dirname($tmpXlsx))) {
                @mkdir(dirname($tmpXlsx), 0775, true);
            }

            // Ambil konten dari disk (local/S3) lalu tulis ke file lokal
            $content = Storage::disk($disk)->get($excel);
            file_put_contents($tmpXlsx, $content);

            if (!is_file($tmpXlsx) || filesize($tmpXlsx) === 0) {
                @unlink($tmpXlsx);
                continue;
            }

            // Buat preview (batasi 40 baris)
            $preview = $this->readExcelPreview($tmpXlsx, 40);
            @unlink($tmpXlsx);

            $cellCount = count($preview['rows']) * max(1, count($preview['columns']));
            $totalCells += $cellCount;

            $parts[] = [
                'source'  => 'File #'.($idx+1),
                'title'   => $title,
                'year'    => (int)($d['tahun'] ?? 0),
                'columns' => $preview['columns'],
                'rows'    => $preview['rows'],
            ];
            $sources[] = ['title'=>$title, 'url'=>$d['url'] ?? null];

            if ($totalCells > 4000) break; // pengaman token
        }

        if (!$parts) {
            return ['answer'=>'Gagal membaca dataset.','insights'=>[],'viz'=>[],'data_preview'=>[],'sources'=>[]];
        }

        // Prompt naratif + instruksi terstruktur
        $prompt = <<<PROMPT
        Anda adalah asisten analisis data yang membantu pengguna memahami dataset pemerintah Kabupaten Hulu Sungai Utara. 
        
        Berdasarkan data JSON yang diberikan, berikan jawaban yang MUDAH DIPAHAMI dalam bahasa Indonesia yang natural.
        
        TUGAS ANDA:
        1) Berikan jawaban naratif yang menjelaskan temuan utama dari data (1-2 paragraf)
        2) Sertakan insights penting dalam bentuk poin-poin
        3) Rekomendasikan visualisasi yang tepat
        4) Sertakan preview data yang relevan
        
        PENTING: Jawab dalam format JSON yang VALID dengan struktur berikut:
        {
          "answer": "Jawaban naratif dalam bahasa Indonesia yang mudah dipahami, tanpa format JSON di dalamnya",
          "insights": ["Poin insight 1", "Poin insight 2"],
          "viz": [
            {
              "library": "chartjs",
              "type": "line|bar|scatter",
              "x": "nama_kolom_x",
              "y": ["nama_kolom_y1", "nama_kolom_y2"],
              "series_meta": [{"label": "Label grafik", "source": "File #1"}],
              "options": {"title": "Judul Grafik"}
            }
          ],
          "data_preview": [
            {"source": "File #1", "rows": [{"kolom1": "nilai1", "kolom2": "nilai2"}]}
          ]
        }
        
        Pertanyaan pengguna: "{$question}"
        
        Jawab dalam JSON yang valid dan pastikan field "answer" berisi teks naratif biasa, BUKAN JSON nested.
        PROMPT;

        // Kirim prompt + JSON parts sebagai teks
        $content = [
            ['type'=>'input_text', 'text'=>$prompt],
            ['type'=>'input_text', 'text'=>json_encode(['datasets'=>$parts], JSON_UNESCAPED_UNICODE)]
        ];

        $payload = [
            'model' => env('OPENAI_MODEL','gpt-4o-mini'),
            'input' => [[ 'role'=>'user', 'content'=>$content ]],
            'stream'=> false
            // Jika ingin output selalu JSON strict, aktifkan response_format json_schema di sini.
        ];

        $resp = $this->http->post('responses', [
            'headers' => [
                'Authorization' => 'Bearer '.env('OPENAI_API_KEY'),
                'Content-Type'  => 'application/json',
            ],
            'json'    => $payload,
            'timeout' => (int) env('OPENAI_TIMEOUT', 180),
        ]);

        $data = json_decode($resp->getBody()->getContents(), true);
        $text = $data['output'][0]['content'][0]['text'] ?? '';

        // Parse JSON secara toleran
        $parsed = $this->parseJsonLoose($text);
        if (!is_array($parsed)) {
            // fallback: bungkus sebagai jawaban plain, pastikan bukan JSON mentah
            $cleanAnswer = $this->sanitizeAnswer($text);
            return [
                'answer' => $cleanAnswer,
                'insights'=>[],
                'viz'=>[],
                'data_preview'=>[],
                'sources'=>$sources
            ];
        }

        // Gabungkan sumber & normalisasi bidang
        $parsed['sources']      = $sources;
        $parsed['answer']       = $this->sanitizeAnswer($parsed['answer'] ?? $text);
        $parsed['data_preview'] = $parsed['data_preview'] ?? [];
        $parsed['viz']          = $parsed['viz'] ?? [];
        $parsed['insights']     = $parsed['insights'] ?? [];

        return $parsed;
    }

    /**
     * Helper method to ensure only one final response is emitted per request
     */
    private function emitFinalOnce(\Closure $emit, array $final, array &$responseState): void
    {
        if ($responseState['emitted']) {
            \Log::warning('ChatbotSdiService: Attempted duplicate response emission blocked', [
                'instance_id' => spl_object_id($this),
                'response_state' => $responseState
            ]);
            return; // Already emitted a response, ignore subsequent calls
        }
        
        \Log::info('ChatbotSdiService: Emitting final response', [
            'instance_id' => spl_object_id($this), 
            'answer_length' => strlen($final['answer'] ?? ''),
            'response_state' => $responseState
        ]);
        
        // Validate and sanitize the response structure
        $validatedFinal = $this->validateFinalResponse($final);
        
        $responseState['emitted'] = true;
        $emit('final', $validatedFinal);
    }

    /**
     * Validate and sanitize final response structure
     */
    private function validateFinalResponse(array $response): array
    {
        \Log::info('ChatbotSdiService: Validating response', [
            'has_answer' => isset($response['answer']),
            'answer_type' => gettype($response['answer'] ?? null),
            'answer_preview' => substr($response['answer'] ?? '', 0, 100)
        ]);

        // Ensure required fields exist with proper defaults
        $validated = [
            'answer' => $response['answer'] ?? 'Maaf, belum ada jawaban yang tersedia.',
            'sources' => $response['sources'] ?? [],
            'data_preview' => $response['data_preview'] ?? [],
            'viz' => $response['viz'] ?? [],
            'insights' => $response['insights'] ?? []
        ];

        // Ensure answer is always a string and never raw JSON
        if (!is_string($validated['answer'])) {
            \Log::warning('ChatbotSdiService: Answer is not string', ['type' => gettype($validated['answer'])]);
            if (is_array($validated['answer']) || is_object($validated['answer'])) {
                $validated['answer'] = 'Maaf, terjadi kesalahan dalam memproses jawaban.';
            } else {
                $validated['answer'] = (string)$validated['answer'];
            }
        }

        // Sanitize the answer to ensure it's clean text
        $validated['answer'] = $this->sanitizeAnswer($validated['answer']);

        // Ensure insights is always an array
        if (!is_array($validated['insights'])) {
            $validated['insights'] = [];
        }

        // Ensure sources is always an array
        if (!is_array($validated['sources'])) {
            $validated['sources'] = [];
        }

        \Log::info('ChatbotSdiService: Response validated', [
            'final_answer_length' => strlen($validated['answer']),
            'insights_count' => count($validated['insights']),
            'sources_count' => count($validated['sources'])
        ]);

        return $validated;
    }

    /**
     * Check if a string looks like JSON
     */
    private function looksLikeJson(string $text): bool
    {
        $trimmed = trim($text);
        return (str_starts_with($trimmed, '{') && str_ends_with($trimmed, '}')) ||
               (str_starts_with($trimmed, '[') && str_ends_with($trimmed, ']'));
    }

    /**
     * Sanitize answer to ensure it's never raw JSON
     */
    private function sanitizeAnswer(?string $answer): string
    {
        if (!$answer) {
            return 'Maaf, belum ada jawaban yang tersedia.';
        }

        // If it looks like JSON, try to extract meaningful content
        if ($this->looksLikeJson($answer)) {
            $parsed = json_decode($answer, true);
            if (is_array($parsed)) {
                // Try to extract answer from JSON structure
                if (isset($parsed['answer']) && is_string($parsed['answer'])) {
                    return $parsed['answer'];
                }
                // If no answer field, return error message
                return 'Maaf, terjadi kesalahan dalam memproses jawaban.';
            }
        }

        return $answer;
    }

    private function parseJsonLoose(?string $text): ?array
    {
        if (!$text) return null;

        // Clean the text first
        $text = trim($text);
        
        // Try direct JSON decode first
        $j = json_decode($text, true);
        if (is_array($j) && isset($j['answer'])) return $j;

        // Try to extract JSON from markdown code blocks
        if (preg_match('/```json\s*([\s\S]*?)```/i', $text, $m)) {
            $j = json_decode(trim($m[1]), true);
            if (is_array($j) && isset($j['answer'])) return $j;
        }

        // Try to extract the first complete JSON object
        if (preg_match('/\{[^{}]*(?:\{[^{}]*\}[^{}]*)*\}/s', $text, $m)) {
            $j = json_decode($m[0], true);
            if (is_array($j) && isset($j['answer'])) return $j;
        }
        
        // Try to find JSON that starts with { and ends with }
        $start = strpos($text, '{');
        $end = strrpos($text, '}');
        if ($start !== false && $end !== false && $end > $start) {
            $jsonStr = substr($text, $start, $end - $start + 1);
            $j = json_decode($jsonStr, true);
            if (is_array($j) && isset($j['answer'])) return $j;
        }

        return null;
    }
}