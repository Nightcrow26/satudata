<?php

namespace App\Domain\Publications\Queries;

use App\Models\Publikasi;
use App\Models\Aspek;
use App\Models\Skpd;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FilterPublications
{
    /**
     * Filter dan paginate publikasi dari database.
     * 
     * Menggunakan Eloquent model dengan relasi untuk filter yang akurat.
     */
    public function paginate(
        string $q = '',
        string $sort = 'recent',      // 'recent' | 'oldest' | 'popular' | 'name'
        array $jenis = [],            // Jenis Informasi (aspek)
        array $instansi = [],         // Instansi (Produsen Data)
        array $bidang = [],           // Bidang Urusan (sama dengan aspek)
        int $perPage = 10,
    ): LengthAwarePaginator {
        try {
            $query = Publikasi::with(['aspek', 'skpd', 'user'])
                ->whereIn('status', ['published', 'approved']);

            // Search berdasarkan nama dan deskripsi
            $q = is_string($q) ? trim($q) : '';
            if ($q !== '') {
                $query->where(function ($builder) use ($q) {
                    $builder->where('nama', 'ilike', "%{$q}%")
                           ->orWhere('deskripsi', 'ilike', "%{$q}%")
                           ->orWhere('keyword', 'ilike', "%{$q}%");
                });
            }

            // Filter berdasarkan jenis (aspek)
            if (!empty($jenis)) {
                $query->whereHas('aspek', function ($builder) use ($jenis) {
                    $builder->whereIn('nama', $jenis);
                });
            }

            // Filter berdasarkan instansi
            if (!empty($instansi)) {
                $query->whereHas('skpd', function ($builder) use ($instansi) {
                    $builder->whereIn('singkatan', $instansi);
                });
            }

            // Filter berdasarkan bidang (sama dengan aspek)
            if (!empty($bidang)) {
                $query->whereHas('aspek', function ($builder) use ($bidang) {
                    $builder->whereIn('nama', $bidang);
                });
            }

            // Sortir berdasarkan berbagai opsi
            switch ($sort) {
                case 'oldest':
                    $query->orderBy('created_at', 'asc');
                    break;
                case 'popular':
                    $query->orderBy('download', 'desc');
                    break;
                case 'name':
                    $query->orderBy('nama', 'asc');
                    break;
                case 'recent':
                default:
                    $query->orderBy('created_at', 'desc');
                    break;
            }

            // Paginate dengan onEachSide untuk kontrol pagination
            return $query->paginate($perPage)->onEachSide(1);

        } catch (\Exception $e) {
            // Log error dan return paginator kosong
            \Log::error('Error dalam paginate publikasi:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Return paginator kosong jika ada error
            return new LengthAwarePaginator(
                items: [],
                total: 0,
                perPage: $perPage,
                currentPage: LengthAwarePaginator::resolveCurrentPage() ?: 1,
                options: ['path' => request()->url(), 'query' => request()->query()]
            );
        }
    }

    /**
     * Opsi facet untuk panel filter berdasarkan data database.
     * Mengambil aspek, instansi, dan bidang yang memiliki publikasi.
     */
    public static function facetOptions(): array
    {
        try {
            // Ambil aspek dari database yang memiliki publikasi published/approved
            $aspekOptions = Aspek::join('publikasi', 'aspeks.id', '=', 'publikasi.aspek_id')
                ->whereIn('publikasi.status', ['published', 'approved'])
                ->select('aspeks.nama')
                ->distinct()
                ->orderBy('aspeks.nama')
                ->pluck('aspeks.nama', 'aspeks.nama')
                ->toArray();

            // Ambil instansi dari database yang memiliki publikasi published/approved
            $instansiOptions = Skpd::join('publikasi', 'skpd.id', '=', 'publikasi.instansi_id')
                ->whereIn('publikasi.status', ['published', 'approved'])
                ->select('skpd.singkatan', 'skpd.nama')
                ->distinct()
                ->orderBy('skpd.singkatan')
                ->pluck('skpd.nama', 'skpd.singkatan')
                ->toArray();

            // Bidang sama dengan aspek untuk publikasi
            $bidangOptions = $aspekOptions;

            // Jenis berdasarkan aspek untuk konsistensi
            $jenisOptions = $aspekOptions;

            return [
                'jenis' => $jenisOptions,
                'instansi' => $instansiOptions,
                'bidang' => $bidangOptions,
            ];
        } catch (\Exception $e) {
            // Log error untuk debugging
            \Log::error('Error dalam facetOptions untuk publikasi:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Fallback jika ada error
            return [
                'jenis' => [],
                'instansi' => [],
                'bidang' => [],
            ];
        }
    }
}
