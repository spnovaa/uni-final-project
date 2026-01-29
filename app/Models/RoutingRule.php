<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoutingRule extends Model
{
    protected $fillable = [
        'match_scopes',
        'strategy',
        'provider_model_id',
        'status',
    ];

    protected $casts = [
        'match_scopes' => 'array',
    ];

    public function providerModel(): BelongsTo
    {
        return $this->belongsTo(ProviderModel::class);
    }
}
