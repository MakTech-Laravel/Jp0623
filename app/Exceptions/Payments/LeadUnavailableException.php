<?php

namespace App\Exceptions\Payments;

use RuntimeException;

/**
 * The lead is no longer listed or was just taken by another operator.
 */
class LeadUnavailableException extends RuntimeException
{
    public function __construct(string $message = 'This lead was just purchased by another operator.')
    {
        parent::__construct($message);
    }
}
