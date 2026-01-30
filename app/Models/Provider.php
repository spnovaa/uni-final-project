<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Eloquent model representing an upstream AI provider configuration.
 *
 * Stores base URL, type, status, and encrypted connection config used by provider adapters.
 */
class Provider extends Model
{
    protected $fillable = [
        'name',
        'type',
        'base_url',
        'status',
        'priority',
        'config_encrypted',
    ];

    protected $casts = [
        'config_encrypted' => 'encrypted:array',
    ];

    /**
     * Get the models relationship.
     * @return HasMany
     */
    public function models(): HasMany
    {
        return $this->hasMany(ProviderModel::class);
    }
}
