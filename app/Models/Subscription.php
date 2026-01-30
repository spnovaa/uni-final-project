<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Subscription.
 */
class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'plan_id',
        'status',
        'starts_at',
        'ends_at',
        'renewal_at',
        'canceled_at',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'renewal_at' => 'datetime',
        'canceled_at' => 'datetime',
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
     * Get the plan relationship.
     * @return BelongsTo
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'plan_id');
    }
}
