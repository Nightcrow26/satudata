<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str; 
use Illuminate\Support\Facades\Storage;
use App\Traits\ViewTracker;

class Dataset extends Model
{
    use HasFactory, ViewTracker;

    // Primary key is auto-incrementing id
    // If you use UUIDs, adjust $keyType and $incrementing accordingly

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $table = 'datasets';  
    public $incrementing = false;
    protected $keyType = 'string';
    protected $appends = ['slug']; // supaya ikut saat toArray()

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
        'excel',
        'tahun',
        'metadata',
        'catatan_verif',
        'deskripsi',
        'keyword',
        'view',
        'instansi_id',
        'user_id',
        'aspek_id',
        'bukti_dukung'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'tahun'         => 'integer',
        'metadata'      => 'array',
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
     * Accessor untuk views (plural) agar konsisten dengan frontend
     */
    public function getViewsAttribute(): int
    {
        return $this->view ?? 0;
    }

    /**
     * Accessor to get full URL of the Excel file from S3
     */
    public function getExcelUrlAttribute(): ?string
    {
        if (!$this->excel) {
            return null;
        }

        // Jika sudah berupa URL lengkap, return as is
        if (filter_var($this->excel, FILTER_VALIDATE_URL)) {
            return $this->excel;
        }

        // Generate temporary URL dari S3 (valid 15 menit)
        try {
            return Storage::disk('s3')->temporaryUrl($this->excel, now()->addMinutes(15));
        } catch (\Exception $e) {
            // Fallback jika S3 error
            return null;
        }
    }

    /**
     * Accessor untuk mendapatkan slug dari nama dataset
     */
    public function getSlugAttribute(): string
    {
        return Str::slug(Str::lower($this->nama));
    }

    /**
     * Resolve route model binding untuk public dataset
     * Cari berdasarkan slug terlebih dahulu, kemudian fallback ke UUID
     */
    public function resolveRouteBinding($value, $field = null)       
    {
        // Jika value adalah UUID, langsung cari berdasarkan ID
        if (\Illuminate\Support\Str::isUuid($value)) {
            return $this->where('id', $value)
                ->where(function ($query) {
                    $query->where('status', 'published')
                          ->orWhere('status', 'approved');
                })
                ->with(['aspek', 'skpd', 'user'])
                ->first();
        }

        // Jika bukan UUID, anggap sebagai slug
        // Cari dataset yang slug-nya cocok dengan nilai yang diberikan
        $datasets = $this->where(function ($query) {
                $query->where('status', 'published')
                      ->orWhere('status', 'approved');
            })
            ->with(['aspek', 'skpd', 'user'])
            ->get();

        // Filter berdasarkan slug yang generated
        $matchedDataset = $datasets->first(function ($dataset) use ($value) {
            return $dataset->slug === $value;
        });

        return $matchedDataset;
    }
}