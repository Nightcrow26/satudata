<?php

namespace App\Domain\Walidata\Queries;

use App\Models\Walidata;
use App\Models\Aspek;
use App\Models\Skpd;
use App\Models\Bidang;
use App\Models\Indikator;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FilterWalidata
{
    /**
     * Filter dan paginate walidata dari database menggunakan Eloquent
     */
    public function paginate(
        string $q = '',
        string $sort = 'recent',
        array $aspek = [],
        array $instansi = [],
        array $bidang = [],
        array $indikator = [],
        int $perPage = 10,
    ): LengthAwarePaginator {
        // Jika tabel belum ada → kembalikan paginator kosong (UI tetap jalan)
        if (! Schema::hasTable('walidata')) {
            return new LengthAwarePaginator(
                items: [],
                total: 0,
                perPage: $perPage,
                currentPage: LengthAwarePaginator::resolveCurrentPage() ?: 1,
                options: ['path' => request()->url(), 'query' => request()->query()]
            );
        }

        // Query walidata dengan relasi
        $query = Walidata::with(['aspek', 'skpd', 'bidang', 'indikator', 'user'])->orderBy('verifikasi_data', 'desc');
        // Tidak perlu filter status karena semua data walidata sudah verified

        // Search berdasarkan data, satuan dan indikator
        $q = trim($q);
        if ($q !== '') {
            $query->where(function ($builder) use ($q) {
                $builder->where('data', 'like', "%{$q}%")
                        ->orWhere('satuan', 'like', "%{$q}%")
                        ->orWhereHas('indikator', function ($indikatorQuery) use ($q) {
                            $indikatorQuery->where('uraian_indikator', 'like', "%{$q}%");
                        });
            });
        }

        // Filter berdasarkan aspek
        if (!empty($aspek)) {
            $query->whereHas('aspek', function ($builder) use ($aspek) {
                $builder->whereIn('nama', $aspek);
            });
        }

        // Filter berdasarkan instansi/SKPD
        if (!empty($instansi)) {
            $query->whereHas('skpd', function ($builder) use ($instansi) {
                $builder->whereIn('singkatan', $instansi)
                        ->orWhereIn('nama', $instansi);
            });
        }

        // Filter berdasarkan bidang
        if (!empty($bidang)) {
            $query->whereHas('bidang', function ($builder) use ($bidang) {
                $builder->whereIn('uraian_bidang', $bidang);
            });
        }

        // Filter berdasarkan indikator
        if (!empty($indikator)) {
            $query->whereHas('indikator', function ($builder) use ($indikator) {
                $builder->whereIn('uraian_indikator', $indikator);
            });
        }

        // Sorting
        switch ($sort) {
            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;
            case 'popular':
                $query->orderBy('view', 'desc');
                break;
            case 'name':
                $query->join('indikators', 'walidata.indikator_id', '=', 'indikators.id')
                      ->orderBy('indikators.uraian_indikator', 'asc')
                      ->select('walidata.*');
                break;
            case 'recent':
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }

        // Kembalikan model Eloquent langsung untuk kompatibilitas dengan template
        return $query->paginate($perPage);
    }

    /**
     * Dapatkan pilihan filter dari database
     */
    public function facetOptions(): array
    {
        if (! Schema::hasTable('walidata')) {
            return [
                'aspek' => [],
                'instansi' => [],
                'bidang' => [],
                'indikator' => [],
            ];
        }

        try {
            // Gunakan query join yang lebih efisien untuk mendapatkan distinct values
            $aspekOptions = DB::table('walidata')
                ->join('aspeks', 'walidata.aspek_id', '=', 'aspeks.id')
                ->whereNotNull('walidata.aspek_id')
                ->whereNotNull('aspeks.nama')
                ->where('aspeks.nama', '!=', '')
                ->distinct()
                ->orderBy('aspeks.nama')
                ->pluck('aspeks.nama', 'aspeks.nama')
                ->filter() // Remove any null values
                ->toArray();

            $instansiOptions = DB::table('walidata')
                ->join('skpd', 'walidata.skpd_id', '=', 'skpd.id')
                ->whereNotNull('walidata.skpd_id')
                ->whereNotNull('skpd.singkatan')
                ->whereNotNull('skpd.nama')
                ->where('skpd.singkatan', '!=', '')
                ->where('skpd.nama', '!=', '')
                ->distinct()
                ->orderBy('skpd.singkatan')
                ->pluck('skpd.nama', 'skpd.singkatan')
                ->filter() // Remove any null values
                ->toArray();

            $bidangOptions = DB::table('walidata')
                ->join('bidangs', 'walidata.bidang_id', '=', 'bidangs.id')
                ->whereNotNull('walidata.bidang_id')
                ->whereNotNull('bidangs.uraian_bidang')
                ->where('bidangs.uraian_bidang', '!=', '')
                ->distinct()
                ->orderBy('bidangs.uraian_bidang')
                ->pluck('bidangs.uraian_bidang', 'bidangs.uraian_bidang')
                ->filter() // Remove any null values
                ->toArray();

            $indikatorOptions = DB::table('walidata')
                ->join('indikators', 'walidata.indikator_id', '=', 'indikators.id')
                ->whereNotNull('walidata.indikator_id')
                ->whereNotNull('indikators.uraian_indikator')
                ->where('indikators.uraian_indikator', '!=', '')
                ->distinct()
                ->orderBy('indikators.uraian_indikator')
                ->pluck('indikators.uraian_indikator', 'indikators.uraian_indikator')
                ->filter() // Remove any null values
                ->toArray();

            return [
                'aspek' => $aspekOptions,
                'instansi' => $instansiOptions,
                'bidang' => $bidangOptions,
                'indikator' => $indikatorOptions,
            ];
        } catch (\Exception $e) {
            // Log error untuk debugging
            \Log::error('Error dalam facetOptions FilterWalidata:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Jika ada error (misalnya tabel belum ada), return array kosong
            return [
                'aspek' => [],
                'instansi' => [],
                'bidang' => [],
                'indikator' => [],
            ];
        }
    }
}