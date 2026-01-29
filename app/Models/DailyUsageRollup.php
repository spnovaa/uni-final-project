<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function apiKey(): BelongsTo
    {
        return $this->belongsTo(ApiKey::class);
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    public function providerModel(): BelongsTo
    {
        return $this->belongsTo(ProviderModel::class);
    }
}
