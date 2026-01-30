<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class AuditLog.
 */
class AuditLog extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'actor_user_id',
        'action',
        'target_type',
        'target_id',
        'meta',
        'created_at',
    ];

    protected $casts = [
        'meta' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * Get the actor relationship.
     * @return BelongsTo
     */
    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }
}
