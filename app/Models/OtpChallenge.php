<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Eloquent model representing an OTP challenge record.
 *
 * Stores hashed OTP code, destination/channel, expiry, and attempt counters for verification.
 */
class OtpChallenge extends Model
{
    protected $fillable = [
        'user_id',
        'channel',
        'destination',
        'code_hash',
        'expires_at',
        'attempts',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];
}
