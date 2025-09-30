<?php

namespace App\Services\Data;

use App\Models\Dataset;
use Illuminate\Support\Str;

class DatasetSearchService
{
    /**
     * @return array<int,array{id:string,nama:string,score:float,tahun:int,excel:?string,url:?string}>
     */
    public function search(array $keywords, ?int $fromYear = null, ?int $toYear = null, ?int $exactYear = null, int $limit = 5): array
    {
        $q = Dataset::query()->select('id','nama','tahun','excel','deskripsi','keyword');

        if (!empty($keywords)) {
            $q->where(function($w) use ($keywords) {
                foreach ($keywords as $kw) {
                    $w->orWhere('nama', 'ilike', '%'.$kw.'%')
                      ->orWhere('keyword','ilike','%'.$kw.'%')
                      ->orWhere('deskripsi','ilike','%'.$kw.'%')
                      ->where('status', 'published');
                }
            });
        }

        if ($exactYear) {
            $q->where('tahun', $exactYear);
        } elseif ($fromYear || $toYear) {
            if ($fromYear) $q->where('tahun', '>=', $fromYear);
            if ($toYear)   $q->where('tahun', '<=', $toYear);
        }

        $rows = $q->orderByDesc('tahun')->limit($limit)->get();

        // skor sederhana: jumlah keyword match di nama+keyword
        return $rows->map(function($r) use ($keywords) {
            $hay = Str::lower(($r->nama ?? '').' '.($r->keyword ?? ''));
            $score = 0.0;
            foreach ($keywords as $kw) if (Str::contains($hay, Str::lower($kw))) $score += 1.0;
            return [
                'id'    => (string)$r->id,
                'nama'  => (string)$r->nama,
                'tahun' => (int)($r->tahun ?? 0),
                'excel' => $r->excel,
                'url'   => route('admin.dataset.show', $r->id), // sesuaikan route show dataset Anda
                'score' => max($score, 0.1),
            ];
        })->sortByDesc('score')->values()->all();
    }
}
