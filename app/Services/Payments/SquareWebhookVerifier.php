<?php

namespace App\Services\Payments;

/**
 * Validates Square webhook signatures.
 *
 * Square signs `notification URL + raw request body` with HMAC-SHA256 using the
 * subscription's signature key and sends the base64 digest in the
 * `x-square-hmacsha256-signature` header.
 */
class SquareWebhookVerifier
{
    public const SIGNATURE_HEADER = 'x-square-hmacsha256-signature';

    public function isValid(string $rawBody, ?string $signatureHeader): bool
    {
        $key = (string) config('services.square.webhook_signature_key');

        if ($key === '' || $signatureHeader === null || $signatureHeader === '') {
            return false;
        }

        return hash_equals($this->signatureFor($rawBody), $signatureHeader);
    }

    /**
     * The signature Square would send for the given raw body.
     */
    public function signatureFor(string $rawBody): string
    {
        return base64_encode(hash_hmac(
            'sha256',
            $this->notificationUrl().$rawBody,
            (string) config('services.square.webhook_signature_key'),
            true,
        ));
    }

    /**
     * Must be byte-identical to the URL configured in the Square subscription.
     */
    public function notificationUrl(): string
    {
        return (string) (config('services.square.webhook_url') ?: url('/webhooks/square'));
    }
}
