<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str; 

class Skpd extends Model
{
    protected $table = 'skpd';  
    public $incrementing = false;
    protected $keyType = 'string';
    protected $appends = ['slug'];

    protected $fillable = [
        'id','nama', 'singkatan', 'alamat', 'telepon', 'foto','unor_id', 'unor_induk_id', 'diatasan_id'
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
     * Relasi ke datasets
     */
    public function datasets(): HasMany
    {
        return $this->hasMany(Dataset::class, 'instansi_id', 'id');
    }

    /**
     * Relasi ke publikasi
     */
    public function publikasis(): HasMany
    {
        return $this->hasMany(Publikasi::class, 'instansi_id', 'id');
    }

    /**
     * Relasi ke walidata
     */
    public function walidata(): HasMany
    {
        return $this->hasMany(Walidata::class, 'skpd_id', 'id');
    }

    /**
     * Get the full URL for the foto attribute
     */
    public function getFotoUrlAttribute(): ?string
    {
        if (!$this->foto) {
            return null;
        }

        // If it's already a full URL, return as is
        if (filter_var($this->foto, FILTER_VALIDATE_URL)) {
            return $this->foto;
        }

        // Otherwise, generate S3 temporary URL
        try {
            return \Illuminate\Support\Facades\Storage::disk('s3')->temporaryUrl($this->foto, now()->addMinutes(15));
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Alias for foto_url for consistency
     */
    public function getLogoUrlAttribute(): ?string
    {
        return $this->foto_url;
    }

    public function getSlugAttribute(): string
    {
        return Str::slug(Str::lower($this->nama));
    }
}
