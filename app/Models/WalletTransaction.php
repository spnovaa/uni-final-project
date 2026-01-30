<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class WalletTransaction.
 */
class WalletTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'wallet_id',
        'type',
        'amount',
        'reason',
        'ref_type',
        'ref_id',
        'meta',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'meta' => 'array',
    ];

    /**
     * Get the wallet relationship.
     * @return BelongsTo
     */
    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }
}
