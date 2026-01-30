<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class ApiClient.
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
