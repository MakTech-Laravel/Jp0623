<?php

namespace App\Exceptions\Payments;

use RuntimeException;

/**
 * Square credentials are missing, so no payment can be attempted.
 */
class SquareNotConfiguredException extends RuntimeException
{
    public function __construct(string $message = 'Square payments are not configured.')
    {
        parent::__construct($message);
    }

    /**
     * Message that is safe to show to the operator.
     */
    public function userMessage(): string
    {
        return 'Payments are temporarily unavailable. Please try again later.';
    }
}
