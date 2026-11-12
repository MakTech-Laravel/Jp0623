<?php

namespace App\Exceptions\Payments;

use RuntimeException;

/**
 * The charge definitively failed. The message is safe to show to the operator.
 */
class PaymentFailedException extends RuntimeException
{
    public static function fromSquare(SquarePaymentException $exception): self
    {
        return new self(self::messageFor($exception), 0, $exception);
    }

    public static function fromCode(?string $code): self
    {
        return new self(self::messageForCode($code));
    }

    private static function messageFor(SquarePaymentException $exception): string
    {
        if ($exception->isConfigurationProblem()) {
            return 'Payments are temporarily unavailable. Please try again later.';
        }

        return self::messageForCode($exception->errorCode);
    }

    private static function messageForCode(?string $code): string
    {
        return match (true) {
            $code === 'GENERIC_DECLINE', $code === 'CARD_DECLINED' => 'Your card was declined.',
            $code === 'INSUFFICIENT_FUNDS' => 'Your card has insufficient funds.',
            $code === 'CARD_EXPIRED', str_starts_with((string) $code, 'INVALID_EXPIRATION') => 'Your card has expired.',
            $code === 'CVV_FAILURE' => 'The security code is incorrect.',
            $code === 'ADDRESS_VERIFICATION_FAILURE', $code === 'INVALID_POSTAL_CODE' => 'The postal code does not match.',
            $code === 'CARD_DECLINED_VERIFICATION_REQUIRED' => 'Your bank needs extra verification. Please try again.',
            default => 'Payment could not be completed. You were not charged.',
        };
    }
}
