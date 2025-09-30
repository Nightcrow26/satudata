<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Bidang extends Model
{
    protected $table = 'bidangs';  
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'kode_bidang', 'uraian_bidang',
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
     * Relasi ke walidata
     */
    public function walidata(): HasMany
    {
        return $this->hasMany(Walidata::class, 'bidang_id', 'id');
    }
}
