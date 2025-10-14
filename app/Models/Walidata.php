<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Support\Str;
use App\Traits\ViewTracker;

class Walidata extends Model
{
    use HasFactory;
    use HasUuids; // Hapus baris ini jika id selalu Anda set dari "idtransaksi".
    use ViewTracker;

    /** =====================
     *  Konfigurasi dasar
     *  ===================== */
    protected $table = 'walidata';

    // Primary key UUID (string) dan tidak auto-increment
    public $incrementing = false;
    protected $keyType = 'string';
    protected $appends = ['slug']; // supaya ikut saat toArray()

    // Sesuaikan mass assignment
    protected $fillable = [
        'id',
        'satuan',
        'tahun',
        'data',
        'verifikasi_data',
        'skpd_id',
        'user_id',
        'aspek_id',
        'indikator_id',
        'bidang_id',
        'view',
    ];

    // Cast seperlunya; 'data' Anda simpan string pada migrasi final.
    protected $casts = [
        'tahun'      => 'string',
        'data'       => 'string', // Ubah ke 'array' jika kelak kolomnya dijadikan JSON.
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];


    public function skpd()
    {
        return $this->belongsTo(Skpd::class, 'skpd_id', 'id')->withDefault();
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id')->withDefault();
    }

    public function aspek()
    {
        return $this->belongsTo(Aspek::class, 'aspek_id', 'id')->withDefault();
    }

    public function indikator()
    {
        return $this->belongsTo(Indikator::class, 'indikator_id', 'id')->withDefault([
            'nama' => 'Indikator Tidak Ditemukan',
            'definisi' => 'Definisi tidak tersedia',
        ]);
    }

    public function bidang()
    {
        return $this->belongsTo(Bidang::class, 'bidang_id', 'id')->withDefault();
    }

    /**
     * Accessor untuk views (plural) agar konsisten dengan frontend  
     */
    public function getViewsAttribute(): int
    {
        return $this->view ?? 0;
    }

    /**
     * Accessor untuk mendapatkan slug dari kombinasi indikator, tahun, dan satuan
     */
    public function getSlugAttribute(): string
    {
        $parts = [];
        
        // Ambil nama indikator jika ada
        if ($this->indikator && $this->indikator->uraian_indikator) {
            $parts[] = $this->indikator->uraian_indikator;
        } elseif ($this->indikator && $this->indikator->nama) {
            $parts[] = $this->indikator->nama;
        }
        
        // Tambahkan tahun jika ada
        if ($this->tahun) {
            $parts[] = $this->tahun;
        }
        
        // Tambahkan satuan jika ada
        if ($this->satuan) {
            $parts[] = $this->satuan;
        }
        
        // Jika tidak ada bagian yang bisa digunakan, gunakan 'walidata' + tahun
        if (empty($parts)) {
            $parts = ['walidata', $this->tahun ?: 'tanpa-tahun'];
        }
        
        return Str::slug(Str::lower(implode(' ', $parts)));
    }

    /**
     * Resolve route model binding untuk public walidata
     * Cari berdasarkan slug terlebih dahulu, kemudian fallback ke UUID
     */
    public function resolveRouteBinding($value, $field = null)
    {
        // Jika value adalah UUID, langsung cari berdasarkan ID
        if (Str::isUuid($value)) {
            return $this->where('id', $value)
                ->with(['aspek', 'skpd', 'user', 'indikator', 'bidang'])
                ->first();
        }

        // Jika bukan UUID, anggap sebagai slug
        // Cari walidata yang slug-nya cocok dengan nilai yang diberikan
        $walidatas = $this->with(['aspek', 'skpd', 'user', 'indikator', 'bidang'])
            ->get();

        // Filter berdasarkan slug yang generated
        $matchedWalidata = $walidatas->first(function ($walidata) use ($value) {
            return $walidata->slug === $value;
        });

        return $matchedWalidata;
    }
}