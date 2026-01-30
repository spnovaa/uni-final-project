<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Eloquent model representing an API client (a container for API keys).
 *
 * API clients group multiple keys under a single user for better organization and reporting.
 */
class ApiClient extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'status',
    ];

    /**
     * Get the user relationship.
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the keys relationship.
     * @return HasMany
     */
    public function keys(): HasMany
    {
        return $this->hasMany(ApiKey::class);
    }
}
