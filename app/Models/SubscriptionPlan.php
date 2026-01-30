<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class SubscriptionPlan.
 */
class SubscriptionPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'price',
        'currency',
        'period',
        'included_credits',
        'rate_limits',
        'features',
        'status',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'included_credits' => 'decimal:2',
        'rate_limits' => 'array',
        'features' => 'array',
    ];

    /**
     * Get the subscriptions relationship.
     * @return HasMany
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class, 'plan_id');
    }
}
