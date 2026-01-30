<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Eloquent model representing a provider model configuration.
 *
 * Stores a provider's `model_key` along with billable pricing config and capabilities used by
 * the gateway for routing and cost calculation.
 */
class ProviderModel extends Model
{
    protected $fillable = [
        'provider_id',
        'model_key',
        'capabilities',
        'pricing_config',
        'status',
    ];

    protected $casts = [
        'capabilities' => 'array',
        'pricing_config' => 'array',
    ];

    /**
     * Get the provider relationship.
     * @return BelongsTo
     */
    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }
}
