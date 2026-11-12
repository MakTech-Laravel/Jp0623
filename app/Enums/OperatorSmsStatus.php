<?php

namespace App\Enums;

enum OperatorSmsStatus: string
{
    case PENDING = 'pending';
    case DONE = 'done';
    case PARTIAL = 'partial';
    case FAILED = 'failed';
    case SKIPPED = 'skipped';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::DONE => 'Done',
            self::PARTIAL => 'Partial',
            self::FAILED => 'Failed',
            self::SKIPPED => 'Skipped',
        };
    }

    /**
     * Client-facing explanation of what this column means.
     */
    public function description(): string
    {
        return match ($this) {
            self::PENDING => 'SMS alert to nearby operators is queued or still sending.',
            self::DONE => 'Nearby operators within 100 miles were notified by SMS.',
            self::PARTIAL => 'Some nearby operators were notified; others failed.',
            self::FAILED => 'SMS alert to nearby operators could not be sent.',
            self::SKIPPED => 'No SMS sent (Twilio off, missing location, or no operators nearby).',
        };
    }
}
