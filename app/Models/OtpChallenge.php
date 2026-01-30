<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class OtpChallenge.
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
