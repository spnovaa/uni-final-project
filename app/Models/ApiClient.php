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
     * User.
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Keys.
     * @return HasMany
     */
    public function keys(): HasMany
    {
        return $this->hasMany(ApiKey::class);
    }
}
