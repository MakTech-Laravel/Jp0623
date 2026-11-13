<?php

namespace App\Models;

use Database\Factories\OperatorMembershipFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property string $status
 * @property int|null $monthly_price
 * @property string|null $square_customer_id
 * @property string|null $square_card_id
 * @property string|null $card_brand
 * @property string|null $card_last4
 * @property Carbon|null $current_period_start
 * @property Carbon|null $current_period_end
 * @property Carbon|null $next_billing_at
 * @property Carbon|null $canceled_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class OperatorMembership extends Model
{
    /** @use HasFactory<OperatorMembershipFactory> */
    use HasFactory;

    public const STATUS_ACTIVE_FREE = 'active_free';

    public const STATUS_ACTIVE = 'active';

    public const STATUS_PAST_DUE = 'past_due';

    public const STATUS_CANCELED = 'canceled';

    public const STATUS_REQUIRED = 'required';

    protected $fillable = [
        'user_id',
        'status',
        'monthly_price',
        'square_customer_id',
        'square_card_id',
        'card_brand',
        'card_last4',
        'current_period_start',
        'current_period_end',
        'next_billing_at',
        'canceled_at',
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<MembershipPayment, $this>
     */
    public function payments(): HasMany
    {
        return $this->hasMany(MembershipPayment::class);
    }

    public function allowsMarketplaceAccess(): bool
    {
        return in_array($this->status, [self::STATUS_ACTIVE_FREE, self::STATUS_ACTIVE], true);
    }

    public function needsPayment(): bool
    {
        return in_array($this->status, [self::STATUS_REQUIRED, self::STATUS_PAST_DUE], true);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'monthly_price' => 'integer',
            'current_period_start' => 'datetime',
            'current_period_end' => 'datetime',
            'next_billing_at' => 'datetime',
            'canceled_at' => 'datetime',
        ];
    }
}
