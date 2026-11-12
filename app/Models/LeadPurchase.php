<?php

namespace App\Models;

use Database\Factories\LeadPurchaseFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property int $id
 * @property int $user_id
 * @property string $leadable_type
 * @property int $leadable_id
 * @property int $price Price in whole dollars (kept for display).
 * @property string $status
 * @property string|null $lead_lock Non-null while pending/paid; unique per lead.
 * @property int $amount_cents
 * @property string $currency
 * @property string $payment_provider
 * @property string|null $idempotency_key
 * @property string|null $square_payment_id
 * @property string|null $square_order_id
 * @property string|null $square_status
 * @property string|null $square_receipt_url
 * @property string|null $square_receipt_number
 * @property string|null $card_brand
 * @property string|null $card_last4
 * @property array<string, mixed>|null $square_payload
 * @property string|null $failure_code
 * @property string|null $failure_category
 * @property string|null $failure_message
 */
#[Fillable([
    'user_id',
    'leadable_type',
    'leadable_id',
    'price',
    'status',
    'lead_lock',
    'amount_cents',
    'currency',
    'payment_provider',
    'idempotency_key',
    'square_payment_id',
    'square_order_id',
    'square_status',
    'square_receipt_url',
    'square_receipt_number',
    'card_brand',
    'card_last4',
    'square_payload',
    'failure_code',
    'failure_category',
    'failure_message',
    'paid_at',
    'refunded_at',
    'square_refund_id',
    'refund_idempotency_key',
    'purchased_at',
])]
class LeadPurchase extends Model
{
    /** @use HasFactory<LeadPurchaseFactory> */
    use HasFactory;

    public const STATUS_PENDING = 'pending';

    public const STATUS_PAID = 'paid';

    public const STATUS_FAILED = 'failed';

    public const STATUS_REFUNDED = 'refunded';

    public const STATUS_VOID = 'void';

    public const PROVIDER_SQUARE = 'square';

    public const PROVIDER_LEGACY = 'legacy';

    /**
     * The value stored in `lead_lock` while an operator holds a lead.
     */
    public static function leadKey(string $source, int $id): string
    {
        return $source.':'.$id;
    }

    /**
     * @param  Builder<LeadPurchase>  $query
     * @return Builder<LeadPurchase>
     */
    public function scopePaid(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PAID);
    }

    /**
     * Purchases that currently hold a lead (pending or paid).
     *
     * @param  Builder<LeadPurchase>  $query
     * @return Builder<LeadPurchase>
     */
    public function scopeHoldingLead(Builder $query): Builder
    {
        return $query->whereNotNull('lead_lock');
    }

    public function isPaid(): bool
    {
        return $this->status === self::STATUS_PAID;
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function leadable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'amount_cents' => 'integer',
            'square_payload' => 'array',
            'purchased_at' => 'datetime',
            'paid_at' => 'datetime',
            'refunded_at' => 'datetime',
        ];
    }
}
