<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $mode
 * @property int $monthly_price
 * @property int $grace_days
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class MembershipSetting extends Model
{
    public const MODE_FREE = 'free';

    public const MODE_PAID = 'paid';

    public const MODES = [
        self::MODE_FREE,
        self::MODE_PAID,
    ];

    protected $fillable = [
        'mode',
        'monthly_price',
        'grace_days',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'monthly_price' => 'integer',
            'grace_days' => 'integer',
        ];
    }

    public function isPaid(): bool
    {
        return $this->mode === self::MODE_PAID;
    }

    public function isFree(): bool
    {
        return $this->mode === self::MODE_FREE;
    }
}
