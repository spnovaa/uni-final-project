<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Eloquent model representing aggregated daily usage.
 *
 * Rollups are computed from `usage_records` to speed up reporting and invoice generation.
 */
class DailyUsageRollup extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'user_id',
        'api_key_id',
        'provider_id',
        'provider_model_id',
        'metric',
        'quantity',
        'total_cost',
    ];

    protected $casts = [
        'date' => 'date',
        'quantity' => 'decimal:4',
        'total_cost' => 'decimal:4',
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
     * Get the API key relationship.
     * @return BelongsTo
     */
    public function apiKey(): BelongsTo
    {
        return $this->belongsTo(ApiKey::class);
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
     * Get the provider model relationship.
     * @return BelongsTo
     */
    public function providerModel(): BelongsTo
    {
        return $this->belongsTo(ProviderModel::class);
    }
}
