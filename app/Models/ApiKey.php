<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Eloquent model representing an API key used to authenticate gateway requests.
 *
 * Stores only a hashed secret (`key_hash`) plus a lookup prefix (`key_prefix`) for fast matching.
 */
class ApiKey extends Model
{
    protected $fillable = [
        'api_client_id',
        'key_prefix',
        'key_hash',
        'scopes',
        'rate_limit_per_min',
        'allowed_ips',
        'expires_at',
        'revoked_at',
    ];

    protected $casts = [
        'scopes' => 'array',
        'allowed_ips' => 'array',
        'expires_at' => 'datetime',
        'revoked_at' => 'datetime',
    ];

    /**
     * Get the client relationship.
     * @return BelongsTo
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(ApiClient::class, 'api_client_id');
    }
}
