<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Indikator extends Model
{
    protected $table = 'indikators';  
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'kode_indikator', 'uraian_indikator', 'bidang_id',
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

    public function bidang(): BelongsTo
    {
        return $this->belongsTo(bidang::class, 'bidang_id', 'id');
    }

    /**
     * Relasi ke walidata
     */
    public function walidata(): HasMany
    {
        return $this->hasMany(Walidata::class, 'indikator_id', 'id');
    }
}
