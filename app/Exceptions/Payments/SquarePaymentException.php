<?php

namespace App\Exceptions\Payments;

use Illuminate\Http\Client\Response;
use RuntimeException;
use Throwable;

/**
 * A Square API call failed.
 *
 * The message only carries Square's category/code, never the access token or
 * the card token. {@see self::isOutcomeUnknown()} tells the caller whether the
 * charge may still have happened (so the purchase must stay pending).
 */
class SquarePaymentException extends RuntimeException
{
    public function __construct(
        public readonly ?string $category = null,
        public readonly ?string $errorCode = null,
        public readonly ?string $detail = null,
        public readonly ?int $httpStatus = null,
        public readonly bool $connectionFailed = false,
        ?Throwable $previous = null,
    ) {
        parent::__construct(
            'Square request failed: '.($errorCode ?? ($connectionFailed ? 'CONNECTION_ERROR' : 'UNKNOWN')),
            0,
            $previous,
        );
    }

    public static function fromResponse(Response $response): self
    {
        $error = $response->json('errors.0');

        return new self(
            category: is_array($error) ? ($error['category'] ?? null) : null,
            errorCode: is_array($error) ? ($error['code'] ?? null) : null,
            detail: is_array($error) ? ($error['detail'] ?? null) : null,
            httpStatus: $response->status(),
        );
    }

    public static function connectionFailed(Throwable $previous): self
    {
        return new self(connectionFailed: true, previous: $previous);
    }

    /**
     * True when we cannot tell whether Square processed the request.
     */
    public function isOutcomeUnknown(): bool
    {
        return $this->connectionFailed
            || $this->httpStatus === 429
            || ($this->httpStatus !== null && $this->httpStatus >= 500)
            || in_array($this->category, ['API_ERROR', 'RATE_LIMIT_ERROR'], true);
    }

    /**
     * True for configuration problems that should be logged as errors.
     */
    public function isConfigurationProblem(): bool
    {
        return in_array($this->category, ['AUTHENTICATION_ERROR', 'INVALID_REQUEST_ERROR'], true);
    }
}
