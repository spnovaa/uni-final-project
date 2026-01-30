<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Eloquent model representing a routing rule for provider/model selection.
 *
 * Routing rules can be used to implement strategies such as default/fallback routing based on
 * API key scopes or request attributes.
 */
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

    /**
     * Get the provider model relationship.
     * @return BelongsTo
     */
    public function providerModel(): BelongsTo
    {
        return $this->belongsTo(ProviderModel::class);
    }
}
