<?php

namespace App\Services\Data;

class DataAnalyzer
{
    /**
     * @param array<int,array<string,mixed>> $rows
     * @return array{answer:string,data_preview:array<int,array<string,mixed>>,viz:?array}
     */
    public function analyze(array $rows, ?string $colYear, ?string $colValue, ?string $colWilayah, ?array $intentYears = null): array
    {
        if (!$rows) return ['answer'=>'Data kosong.', 'data_preview'=>[], 'viz'=>null];

        // Filter tahun jika diminta
        if ($colYear && $intentYears) {
            $from = $intentYears['from'] ?? null;
            $to   = $intentYears['to'] ?? null;
            $exact= $intentYears['exact'] ?? null;

            $rows = array_values(array_filter($rows, function($r) use ($colYear,$from,$to,$exact) {
                $y = (int)($r[$colYear] ?? 0);
                if ($exact) return $y === (int)$exact;
                if ($from && $y < (int)$from) return false;
                if ($to && $y > (int)$to) return false;
                return true;
            }));
        }

        // 1) Jika ada kolom tahun & nilai → timeseries (sum per tahun)
        if ($colYear && $colValue) {
            $grp = [];
            foreach ($rows as $r) {
                $y = (int)($r[$colYear] ?? 0);
                $v = (float)($r[$colValue] ?? 0);
                $grp[$y] = ($grp[$y] ?? 0) + $v;
            }
            ksort($grp);
            $preview = [];
            foreach ($grp as $y=>$v) $preview[] = [$colYear=>$y, $colValue=>$v];

            $change = $this->percentChange(end($preview)[$colValue] ?? null, $preview[0][$colValue] ?? null);
            $answer = "Tren {$colValue} ".($intentYears? $this->yearsText($intentYears):'').
                      " menunjukkan perubahan ~{$change} dari periode awal ke akhir.";

            $viz = [
                'library'=>'chartjs','type'=>'line','x'=>$colYear,'y'=>[$colValue],
                'options'=>['title'=>"Tren {$colValue} per Tahun"]
            ];
            return ['answer'=>$answer, 'data_preview'=>$preview, 'viz'=>$viz];
        }

        // 2) Jika ada wilayah & nilai → bar per wilayah (limit 12)
        if ($colWilayah && $colValue) {
            $grp = [];
            foreach ($rows as $r) {
                $w = (string)($r[$colWilayah] ?? '');
                $v = (float)($r[$colValue] ?? 0);
                $grp[$w] = ($grp[$w] ?? 0) + $v;
            }
            arsort($grp);
            $preview=[]; $i=0;
            foreach ($grp as $w=>$v) { if (++$i>12) break; $preview[] = [$colWilayah=>$w, $colValue=>$v]; }
            $answer = "Perbandingan {$colValue} antar {$colWilayah} (top 12) ditampilkan pada grafik.";
            $viz = [
                'library'=>'chartjs','type'=>'bar','x'=>$colWilayah,'y'=>[$colValue],
                'options'=>['title'=>"{$colValue} per {$colWilayah}"]
            ];
            return ['answer'=>$answer,'data_preview'=>$preview,'viz'=>$viz];
        }

        // 3) Fallback: tampilkan 10 baris pertama
        $preview = array_slice($rows, 0, 10);
        return ['answer'=>'Menampilkan 10 baris pertama data karena struktur tidak standard.', 'data_preview'=>$preview, 'viz'=>null];
    }

    private function percentChange(?float $last, ?float $first): string
    {
        if ($last === null || $first === null || $first == 0.0) return '—';
        $pct = ($last - $first) / abs($first) * 100.0;
        return number_format($pct, 1).'%';
    }

    private function yearsText(array $y): string
    {
        if (!empty($y['exact'])) return (string)$y['exact'];
        $from = $y['from'] ?? null; $to = $y['to'] ?? null;
        if ($from && $to) return "{$from}–{$to}";
        if ($from) return "sejak {$from}";
        if ($to)   return "hingga {$to}";
        return '';
    }
}
