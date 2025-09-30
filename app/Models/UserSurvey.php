<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserSurvey extends Model
{
    protected $fillable = [
        'session_id',
        'ip_address', 
        'rating',
        'feedback',
        'user_agent'
    ];

    protected $casts = [
        'rating' => 'integer',
    ];

    /**
     * Check if user has already submitted survey in current session
     */
    public static function hasUserCompletedSurvey(string $sessionId, string $ipAddress): bool
    {
        return self::where('session_id', $sessionId)
                   ->where('ip_address', $ipAddress)
                   ->exists();
    }

    /**
     * Create new survey response
     */
    public static function createSurvey(array $data): self
    {
        return self::create([
            'session_id' => $data['session_id'],
            'ip_address' => $data['ip_address'],
            'rating' => $data['rating'],
            'feedback' => $data['feedback'] ?? null,
            'user_agent' => $data['user_agent'] ?? null,
        ]);
    }
}
