<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProviderHealthCheck extends Model
{
    use HasFactory;

    protected $fillable = [
        'provider_id',
        'status',
        'latency_ms',
        'checked_at',
        'meta',
    ];

    protected $casts = [
        'latency_ms' => 'integer',
        'checked_at' => 'datetime',
        'meta' => 'array',
    ];

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }
}
