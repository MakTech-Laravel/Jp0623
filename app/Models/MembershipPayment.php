<?php

namespace App\Models;

use Database\Factories\MembershipPaymentFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property int $operator_membership_id
 * @property int $price
 * @property int $amount_cents
 * @property string $currency
 * @property string $status
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
 * @property Carbon|null $paid_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class MembershipPayment extends Model
{
    /** @use HasFactory<MembershipPaymentFactory> */
    use HasFactory;

    public const STATUS_PENDING = 'pending';

    public const STATUS_PAID = 'paid';

    public const STATUS_FAILED = 'failed';

    public const PROVIDER_SQUARE = 'square';

    protected $fillable = [
        'user_id',
        'operator_membership_id',
        'price',
        'amount_cents',
        'currency',
        'status',
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
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<OperatorMembership, $this>
     */
    public function membership(): BelongsTo
    {
        return $this->belongsTo(OperatorMembership::class, 'operator_membership_id');
    }

    /**
     * @param  Builder<MembershipPayment>  $query
     * @return Builder<MembershipPayment>
     */
    public function scopePaid(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PAID);
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isPaid(): bool
    {
        return $this->status === self::STATUS_PAID;
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
            'paid_at' => 'datetime',
        ];
    }
}
