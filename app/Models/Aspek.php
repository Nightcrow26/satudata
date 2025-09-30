<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str; 

class Aspek extends Model
{
    protected $table = 'aspeks';  
    public $incrementing = false;
    protected $keyType = 'string';
    protected $appends = ['slug']; // supaya ikut saat toArray()

    protected $fillable = [
        'nama', 'warna', 'foto',
    ];

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

    /**
     * Accessor untuk mendapatkan URL foto dari S3
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

    /**
     * Relasi ke datasets
     */
    public function datasets(): HasMany
    {
        return $this->hasMany(Dataset::class, 'aspek_id', 'id');
    }

    /**
     * Relasi ke publikasi
     */
    public function publikasis(): HasMany
    {
        return $this->hasMany(Publikasi::class, 'aspek_id', 'id');
    }

    /**
     * Relasi ke walidata
     */
    public function walidata(): HasMany
    {
        return $this->hasMany(Walidata::class, 'aspek_id', 'id');
    }

    public function getSlugAttribute(): string
    {
        return Str::slug(Str::lower($this->nama));
    }
}
