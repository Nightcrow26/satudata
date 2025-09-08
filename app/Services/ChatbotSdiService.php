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
            $emit('final', $final);
            return;
        }

        // 3B) Ketemu → analisis via JSON preview
        $best = $cands[0];
        $emit('delta', ['text' => "\n📥 Menyiapkan analisis untuk: ".implode(' • ', array_map(fn($d)=>$d['nama'], $picks))."…"]);
        try {
            $final = app(\App\Services\ChatbotSdiService::class)->analyzeMultipleDatasetsViaJson($picks, $message, $emit);
            $emit('final', $final);
            return;
        } catch (\Throwable $e) {
            $emit('delta', ['text' => "\n⚠️ Gagal analisis: ".substr($e->getMessage(),0,200).'…']);
            $fallback = $this->fallbackGeneralAnswer($message);
            $fallback['answer'] = "Catatan: dataset terdeteksi, namun analisis otomatis gagal.\n\n".$fallback['answer'];
            $emit('final', $fallback);
            return; // <— penting: hentikan alur di sini
        }

        // 4) Analisis cepat → data_preview + viz
        $emit('delta', ['text' => "\n🧮 Menganalisis data… "]);
        $cols = $this->reader->detectColumns($rows);
        $result = $this->analyzer->analyze($rows, $cols['tahun'], $cols['nilai'], $cols['wil'], $years);

        // 5) Bangun final payload
        $final = [
            'answer' => $result['answer'],
            'sources' => [[
                'title' => $best['nama'],
                'dataset_id' => $best['id'],
                'url' => $best['url'],
            ]],
            'data_preview' => $result['data_preview'],
            'viz' => $result['viz'],
        ];
        $emit('final', $final);
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
        Anda menerima beberapa potongan JSON berisi cuplikan dataset. Tugas Anda memberikan pengguna jawaban komprehensif berdasarkan data tersebut,  
        berikan jawaban yang berkaitan dengan pertanyaan sesuai langkah berikut:
        1) berikan temuan utama (insights) sepanjang 1-2 paragraf yang relevan dengan pertanyaan.
        2) Rekomendasikan visualisasi yang tepat (struktur objek "viz" seperti contoh).
        3) Sertakan "data_preview" dari cuplikan yang paling relevan (maks 40 baris total).
        4) Tautkan "sources" ke asal dataset yang dilampirkan.
        5) Jawab jelas, gunakan angka yang ada; jika perlu agregasi sederhana (sum/mean), jelaskan singkat.

        Format keluaran JSON:
        {
        "answer": string,
        "insights": string[],
        "viz": [
            {
            "library": "chartjs",
            "type": "line|bar|scatter",
            "x": "<fieldX>",
            "y": ["<fieldY>", "..."],
            "series_meta": [ { "label":"<nama seri>", "source":"File #n" } ],
            "options": { "title":"<judul>" }
            }
        ],
        "data_preview": [
            { "source": "File #n", "rows": [ {<kolom>:<nilai>}, ... ] }
        ]
        }
        Pertanyaan: "{$question}"
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
            // fallback: bungkus sebagai jawaban plain
            return [
                'answer' => $text ?: 'Maaf, belum ada jawaban.',
                'insights'=>[],
                'viz'=>[],
                'data_preview'=>[],
                'sources'=>$sources
            ];
        }

        // Gabungkan sumber & normalisasi bidang
        $parsed['sources']      = $sources;
        $parsed['answer']       = $parsed['answer'] ?? ($text ?: 'Maaf, belum ada jawaban.');
        $parsed['data_preview'] = $parsed['data_preview'] ?? [];
        $parsed['viz']          = $parsed['viz'] ?? [];

        return $parsed;
    }

    private function parseJsonLoose(?string $text): ?array
    {
        if (!$text) return null;

        $j = json_decode($text, true);
        if (is_array($j)) return $j;

        if (preg_match('/```json\s*([\s\S]*?)```/i', $text, $m)) {
            $j = json_decode(trim($m[1]), true);
            if (is_array($j)) return $j;
        }

        if (preg_match('/\{(?:[^{}]|(?R))*\}/s', $text, $m)) {
            $j = json_decode($m[0], true);
            if (is_array($j)) return $j;
        }

        return null;
    }
}