<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str; 

class Publikasi extends Model
{
    use HasFactory;

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
     * Dataset belongs to an instansi (SKPD).
     */
    public function skpd(): BelongsTo
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
     * Accessor to get full URL of the Excel file
     */
    public function getPdfUrlAttribute(): ?string
    {
        if (! $this->pdf) {
            return null;
        }

        return asset('storage/' . $this->pdf);
    }
}
