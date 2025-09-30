<?php

namespace App\Services\Nlp;

use GuzzleHttp\Client;

class IntentExtractor
{
    public function __construct(private ?Client $http = null) {
        $this->http = $this->http ?: new Client(['base_uri' => 'https://api.openai.com/v1/']);
    }

    /**
     * Ekstrak intent terstruktur dari pertanyaan pengguna.
     * - Menggunakan Structured Outputs (json_schema strict) agar selalu valid JSON.
     * - Few-shot + anti-bias: jangan substitusi/topik lain selain yang tertulis.
     * - Post-filter: hanya pertahankan keyword yang muncul di pertanyaan (atau sinonim sah).
     */
    public function extract(string $question): array
    {
        // 0) Normalisasi pertanyaan untuk keperluan post-filter
        $qnorm = $this->normalize($question);

        // 1) Prompt system yang ketat + few-shot anti-bias
        $system = <<<SYS
Anda mengekstrak niat pencarian data dari teks pertanyaan PENGGUNA.
ATURAN KERAS:
- Gunakan HANYA informasi yang eksplisit ada di pertanyaan pengguna.
- JANGAN mengganti, menambah, atau menebak entitas/topik lain yang tidak disebut.
- Jika tahun tidak jelas, kosongkan 'years'.
- jika pengguna menanyakan pertanyaan umum tanpa konteks data, kembalikan keywords kosong ([]).
- 'keywords' berisi 1–6 kata/frasal inti yang benar-benar relevan (hindari kata umum seperti "data", "informasi", "laporan", "statistik").
- Jika entitas tidak ada, kembalikan keywords kosong ([]) dan biarkan bidang lain null/absen.

Contoh POSITIF:
- Q: "Tren jumlah GURU SD 2019-2023 di Hulu Sungai Utara?"
  -> keywords: ["guru", "SD", "Hulu Sungai Utara"], years: {from:2019,to:2023}, geo:"Kabupaten Hulu Sungai Utara", metric_hint:"jumlah"

- Q: "Berapa menara BTS 2021 di Kalsel?"
  -> keywords: ["BTS", "Kalimantan Selatan"], years:{exact:2021}, geo:"Kalimantan Selatan", metric_hint:"jumlah"

Contoh NEGATIF (larangan substitusi):
- Q: "Tren jumlah GURU 2019-2024?"
  SALAH jika menghasilkan ["BTS", "Data", ...] karena 'BTS' dan 'Data' tidak muncul di pertanyaan.

- Q: "Data kemiskinan di Kabupaten HSU?"
  SALAH jika menghasilkan ["BTS", "Data", ...] karena 'BTS' dan 'Data' tidak muncul di pertanyaan.
SYS;

        // 2) Skema Structured Outputs (strict) agar hasil konsisten
        $schema = [
            "name" => "Intent",
            "strict" => true, // penting: patuhi skema dengan ketat
            "schema" => [
                "type" => "object",
                "properties" => [
                    "keywords" => [
                        "type" => "array",
                        "items" => ["type" => "string"],
                        "maxItems" => 6
                    ],
                    "years" => [
                        "type" => "object",
                        "properties" => [
                            "from"  => ["type" => "number"],
                            "to"    => ["type" => "number"],
                            "exact" => ["type" => "number"]
                        ],
                        "additionalProperties" => false
                    ],
                    "geo" => ["type" => "string"],
                    "metric_hint" => ["type" => "string"]
                ],
                "required" => ["keywords"],
                "additionalProperties" => false
            ]
        ];

        $payload = [
            'model' => env('OPENAI_MODEL', 'gpt-4o-mini'),
            'input' => [
                ['role'=>'system', 'content'=>$system],
                ['role'=>'user',   'content'=>$question],
            ],
            'response_format' => [
                'type' => 'json_schema',
                'json_schema' => $schema
            ],
            'stream' => false
        ];

        $resp = $this->http->post('responses', [
            'headers' => [
                'Authorization' => 'Bearer '.env('OPENAI_API_KEY'),
                'Content-Type'  => 'application/json'
            ],
            'json'    => $payload,
            'timeout' => 30
        ]);

        $data = json_decode($resp->getBody()->getContents(), true);
        // Responses API: ambil teks JSON dari output
        $rawText = $data['output'][0]['content'][0]['text'] ?? '{}';
        $parsed  = json_decode($rawText, true) ?: ["keywords"=>[]];

        // 3) Post-filter keyword → cegah "BTS" muncul kalau tidak ada di pertanyaan
        $parsed['keywords'] = $this->postFilterKeywords($qnorm, $parsed['keywords'] ?? []);

        // 4) Rapikan hasil akhir (kosongkan years jika tidak lengkap/invalid)
        if (isset($parsed['years'])) {
            $y = $parsed['years'];
            if (!is_array($y) || (empty($y['from']) && empty($y['to']) && empty($y['exact']))) {
                unset($parsed['years']);
            }
        }

        // Pastikan selalu mengembalikan array sesuai kontrak
        return $parsed;
    }

    /**
     * Filter keyword: hanya pertahankan yang memang ada di pertanyaan,
     * atau sinonim-sinonim resmi yang dipetakan ke istilah inti.
     */
    private function postFilterKeywords(string $qnorm, array $keywords): array
    {
        // Peta sinonim minimal yang umum di SDI; bisa ditambah sesuai kebutuhan domain Anda.
        $synonyms = [
            'guru' => ['guru','tenaga pendidik','pendidik','pengajar'],
            'sekolah' => ['sekolah','sd','smp','sma','smk','madrasah'],
            'penduduk' => ['penduduk','populasi','jumlah jiwa'],
            'kemiskinan' => ['kemiskinan','penduduk miskin','garis kemiskinan'],
            'bts' => ['bts','menara telekomunikasi','base transceiver station'],
            'kesehatan' => ['kesehatan','puskesmas','rs','fasilitas kesehatan'],
            'tenaga kesehatan' => ['tenaga kesehatan','nakes','dokter','perawat','bidan'],
        ];

        // Bangun set kata/frasa yang benar-benar hadir di pertanyaan
        $whitelist = [];
        foreach ($synonyms as $canon => $alts) {
            foreach ($alts as $a) {
                if ($this->contains($qnorm, $a)) {
                    $whitelist[$canon] = true;
                }
            }
        }
        // Juga izinkan kata/frasa apa pun yang literal muncul di pertanyaan
        // (mis. "guru honorer", "Hulu Sungai Utara", "tahun 2024")
        // dengan cara: kata/frasa kandidat harus “terlihat” di qnorm.
        $filtered = [];
        foreach ($keywords as $k) {
            $knorm = $this->normalize($k);
            $ok = false;

            // 1) literal match
            if ($this->contains($qnorm, $knorm)) $ok = true;

            // 2) sinonim → canonical (mis. "tenaga pendidik" → "guru")
            if (!$ok) {
                foreach ($synonyms as $canon => $alts) {
                    foreach ($alts as $a) {
                        if ($knorm === $this->normalize($a) && isset($whitelist[$canon])) {
                            $k = $canon; // map ke istilah inti
                            $ok = true;
                            break 2;
                        }
                    }
                }
            }

            if ($ok) $filtered[] = $k;
        }

        // Unik & rapikan
        $filtered = array_values(array_unique($filtered, SORT_STRING));
        return $filtered;
    }

    private function contains(string $haystack, string $needle): bool
    {
        $needle = $this->normalize($needle);
        return $needle !== '' && str_contains($haystack, $needle);
    }

    private function normalize(string $s): string
    {
        $s = mb_strtolower($s, 'UTF-8');
        $s = preg_replace('/\s+/', ' ', $s);
        $s = trim($s);
        return $s;
    }
}
