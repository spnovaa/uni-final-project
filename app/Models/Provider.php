<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function models(): HasMany
    {
        return $this->hasMany(ProviderModel::class);
    }
}
