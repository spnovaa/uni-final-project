<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }
}
