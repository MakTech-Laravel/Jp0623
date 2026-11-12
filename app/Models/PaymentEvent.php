<?php

namespace App\Models;

use Database\Factories\PaymentEventFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Log of webhook deliveries; the unique `event_id` makes processing idempotent.
 *
 * @property int $id
 * @property string $provider
 * @property string $event_id
 * @property string $type
 * @property array<string, mixed> $payload
 * @property string|null $outcome
 */
#[Fillable(['provider', 'event_id', 'type', 'payload', 'processed_at', 'outcome'])]
class PaymentEvent extends Model
{
    /** @use HasFactory<PaymentEventFactory> */
    use HasFactory;

    public const OUTCOME_APPLIED = 'applied';

    public const OUTCOME_IGNORED = 'ignored';

    public const OUTCOME_UNMATCHED = 'unmatched';

    public const OUTCOME_MISMATCH = 'mismatch';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'processed_at' => 'datetime',
        ];
    }
}
