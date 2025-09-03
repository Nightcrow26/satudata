<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str; 

class Aspek extends Model
{
    protected $table = 'aspeks';  
    public $incrementing = false;
    protected $keyType = 'string';

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
}
