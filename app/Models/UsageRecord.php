<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class UsageRecord.
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
     * Gateway request.
     * @return BelongsTo
     */
    public function gatewayRequest(): BelongsTo
    {
        return $this->belongsTo(GatewayRequest::class);
    }
}
