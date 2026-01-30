<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class InvoiceItem.
 */
class InvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'type',
        'description',
        'quantity',
        'unit_price',
        'line_total',
        'meta',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:4',
        'line_total' => 'decimal:2',
        'meta' => 'array',
    ];

    /**
     * Get the invoice relationship.
     * @return BelongsTo
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}
