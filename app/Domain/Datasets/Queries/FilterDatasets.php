<?php

namespace App\Domain\Datasets\Queries;

use App\Models\Dataset;
use App\Models\Aspek;
use App\Models\Skpd;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FilterDatasets
{
    /**
     * Filter dan paginate datasets dari database menggunakan Eloquent
     */
    public function paginate(
        string $q = '',
        string $sort = 'recent',
        array $aspek = [],
        array $instansi = [],
        array $bidang = [],
        int $perPage = 10,
    ): LengthAwarePaginator {
        // Jika tabel belum ada → kembalikan paginator kosong (UI tetap jalan)
        if (! Schema::hasTable('datasets')) {
            return new LengthAwarePaginator(
                items: [],
                total: 0,
                perPage: $perPage,
                currentPage: LengthAwarePaginator::resolveCurrentPage() ?: 1,
                options: ['path' => request()->url(), 'query' => request()->query()]
            );
        }

        // Query datasets dengan relasi
        $query = Dataset::with(['aspek', 'skpd', 'user'])
            ->where(function ($builder) {
                $builder->where('status', 'published')
                        ->orWhere('status', 'approved'); // termasuk yang sudah disetujui
            });

        // Search berdasarkan nama dan deskripsi
        $q = trim($q);
        if ($q !== '') {
            $query->where(function ($builder) use ($q) {
                $builder->where('nama', 'ilike', "%{$q}%")
                        ->orWhere('deskripsi', 'ilike', "%{$q}%")
                        ->orWhere('keyword', 'ilike', "%{$q}%");
            });
        }

        // Filter berdasarkan aspek
        if (!empty($aspek)) {
            $query->whereHas('aspek', function ($builder) use ($aspek) {
                $builder->whereIn('nama', $aspek);
            });
        }

        // Filter berdasarkan instansi/Produsen Data
        if (!empty($instansi)) {
            $query->whereHas('skpd', function ($builder) use ($instansi) {
                $builder->whereIn('singkatan', $instansi)
                        ->orWhereIn('nama', $instansi);
            });
        }

        // Filter berdasarkan bidang - menggunakan relasi aspek untuk kategori bidang
        if (!empty($bidang)) {
            $query->whereHas('aspek', function ($builder) use ($bidang) {
                $builder->whereIn('nama', $bidang);
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
                $query->orderBy('nama', 'asc');
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
     * Opsi facet dinamis dari database 
     */
    public static function facetOptions(): array
    {
        // Cek apakah tabel ada
        if (!Schema::hasTable('aspeks') || !Schema::hasTable('skpd')) {
            return [
                'aspek' => [],
                'instansi' => [],
                'bidang' => [],
            ];
        }

        try {
            // Ambil aspek dari database yang memiliki dataset published/approved
            $aspekOptions = Aspek::join('datasets', 'aspeks.id', '=', 'datasets.aspek_id')
                ->whereIn('datasets.status', ['published', 'approved'])
                ->select('aspeks.nama')
                ->distinct()
                ->orderBy('aspeks.nama')
                ->pluck('aspeks.nama', 'aspeks.nama')
                ->toArray();

            // Ambil instansi dari database yang memiliki dataset published/approved
            $instansiOptions = Skpd::join('datasets', 'skpd.id', '=', 'datasets.instansi_id')
                ->whereIn('datasets.status', ['published', 'approved'])
                ->select('skpd.singkatan', 'skpd.nama')
                ->distinct()
                ->orderBy('skpd.singkatan')
                ->pluck('skpd.nama', 'skpd.singkatan')
                ->toArray();

            // Bidang sama dengan aspek
            $bidangOptions = $aspekOptions;

            return [
                'aspek' => $aspekOptions,
                'instansi' => $instansiOptions, 
                'bidang' => $bidangOptions,
            ];
        } catch (\Exception $e) {
            // Log error untuk debugging
            \Log::error('Error dalam facetOptions:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Fallback jika ada error
            return [
                'aspek' => [],
                'instansi' => [],
                'bidang' => [],
            ];
        }
    }
}
