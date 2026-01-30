<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class GatewayRequest.
 */
class GatewayRequest extends Model
{
    protected $fillable = [
        'api_key_id',
        'user_id',
        'provider_id',
        'provider_model_id',
        'endpoint',
        'request_hash',
        'status',
        'latency_ms',
    ];

    /**
     * Api key.
     * @return BelongsTo
     */
    public function apiKey(): BelongsTo
    {
        return $this->belongsTo(ApiKey::class);
    }

    /**
     * Get the user relationship.
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the provider relationship.
     * @return BelongsTo
     */
    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    /**
     * Provider model.
     * @return BelongsTo
     */
    public function providerModel(): BelongsTo
    {
        return $this->belongsTo(ProviderModel::class);
    }

    /**
     * Usage records.
     * @return HasMany
     */
    public function usageRecords(): HasMany
    {
        return $this->hasMany(UsageRecord::class);
    }
}
