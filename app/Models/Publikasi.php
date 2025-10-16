<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str; 
use App\Traits\ViewTracker;
use App\Traits\DownloadTracker;

class Publikasi extends Model
{
    use HasFactory, ViewTracker, DownloadTracker;

    // Primary key is auto-incrementing id
    // If you use UUIDs, adjust $keyType and $incrementing accordingly

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $table = 'publikasi';  
    public $incrementing = false;
    protected $keyType = 'string';

    protected static function boot()
    {
        parent::boot();

        // Auto-generate UUID saat creating
        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }

    protected $fillable = [
        'nama',
        'status',
        'pdf',
        'foto',
        'tahun',
        'catatan_verif',
        'deskripsi',
        'keyword',
        'view',
        'download',
        'instansi_id',
        'user_id',
        'aspek_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'tahun'         => 'integer',
        'view'          => 'integer',
        'instansi_id'   => 'string',
        'user_id'       => 'string',
        'aspek_id'      => 'string',
    ];

    /**
     * Relationships
     */

    /**
     * Dataset belongs to an instansi (Produsen Data).
     */
    public function skpd(): BelongsTo
    {
        return $this->belongsTo(Skpd::class, 'instansi_id', 'id');
    }

    /**
     * Alias for skpd relationship - for consistency with frontend terminology
     */
    public function instansi(): BelongsTo
    {
        return $this->belongsTo(Skpd::class, 'instansi_id', 'id');
    }

    /**
     * Dataset belongs to a user.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Dataset belongs to an aspek.
     */
    public function aspek(): BelongsTo
    {
        return $this->belongsTo(Aspek::class, 'aspek_id', 'id');
    }

    /**
     * Accessor to get full URL of the PDF file from S3
     */
    public function getPdfUrlAttribute(): ?string
    {
        if (!$this->pdf) {
            return null;
        }

        // Jika sudah berupa URL lengkap, return as is
        if (filter_var($this->pdf, FILTER_VALIDATE_URL)) {
            return $this->pdf;
        }

        // Generate temporary URL dari S3 (valid 15 menit)
        try {
            return Storage::disk('s3')->temporaryUrl($this->pdf, now()->addMinutes(15));
        } catch (\Exception $e) {
            // Fallback jika S3 error
            return null;
        }
    }

    /**
     * Accessor to get full URL of the foto/image file from S3
     */
    public function getFotoUrlAttribute(): ?string
    {
        if (!$this->foto) {
            return null;
        }

        // Jika sudah berupa URL lengkap, return as is
        if (filter_var($this->foto, FILTER_VALIDATE_URL)) {
            return $this->foto;
        }

        // Generate temporary URL dari S3 (valid 15 menit)
        try {
            return Storage::disk('s3')->temporaryUrl($this->foto, now()->addMinutes(15));
        } catch (\Exception $e) {
            // Fallback jika S3 error
            return null;
        }
    }
}
