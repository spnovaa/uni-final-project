<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Eloquent model representing a billable usage record.
 *
 * Usage records capture per-request quantities and costs for metrics such as tokens, images,
 * and audio, and are used for billing and reporting.
 */
class UsageRecord extends Model
{
    protected $fillable = [
        'gateway_request_id',
        'metric',
        'quantity',
        'unit_cost',
        'total_cost',
    ];

    protected $casts = [
        'quantity' => 'float',
        'unit_cost' => 'float',
        'total_cost' => 'float',
    ];

    /**
     * Get the gateway request relationship.
     * @return BelongsTo
     */
    public function gatewayRequest(): BelongsTo
    {
        return $this->belongsTo(GatewayRequest::class);
    }
}
