<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    // Non-incrementing & tipe string
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
        'name',
        'email',
        'nik',
        'password',
        'skpd_uuid',
        'sk_penunjukan',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->map(fn (string $name) => Str::of($name)->substr(0, 1))
            ->implode('');
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function skpd()
    {
        return $this->belongsTo(Skpd::class, 'skpd_uuid', 'id');
    }

    /**
     * Get SK Penunjukan URL attribute
     */
    public function getSkPenunjukanUrlAttribute(): ?string
    {
        if (!$this->sk_penunjukan) {
            return null;
        }

        // Jika sudah berupa URL lengkap, return as is
        if (filter_var($this->sk_penunjukan, FILTER_VALIDATE_URL)) {
            return $this->sk_penunjukan;
        }

        // Generate temporary URL dari S3 (valid 15 menit) - sama seperti dataset
        try {
            // Check if file exists in S3 before generating URL
            if (!Storage::disk('s3')->exists($this->sk_penunjukan)) {
                return null;
            }
            
            return Storage::disk('s3')->temporaryUrl($this->sk_penunjukan, now()->addMinutes(15));
        } catch (\Exception $e) {
            // Fallback jika S3 error
            return null;
        }
    }
}
