<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
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
}
