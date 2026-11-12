<?php

namespace App\Services\Payments;

use App\Exceptions\Payments\SquareNotConfiguredException;
use App\Exceptions\Payments\SquarePaymentException;
use Carbon\CarbonInterface;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

/**
 * Thin wrapper around Square's REST API (no SDK dependency).
 *
 * Every state-changing call carries an idempotency key, so transient failures
 * (connection errors, 5xx, 429) are retried safely with the same key.
 */
class SquareClient
{
    private const MAX_LIST_PAGES = 5;

    public function isConfigured(): bool
    {
        return filled(config('services.square.access_token'))
            && filled(config('services.square.application_id'))
            && filled(config('services.square.location_id'));
    }

    /**
     * @throws SquareNotConfiguredException
     */
    public function assertConfigured(): void
    {
        if (! $this->isConfigured()) {
            throw new SquareNotConfiguredException;
        }
    }

    public function locationId(): string
    {
        return (string) config('services.square.location_id');
    }

    public function currency(): string
    {
        return strtoupper((string) config('services.square.currency', 'USD'));
    }

    /**
     * The only Square settings the browser may see (never the access token or
     * webhook key), or null when payments are not configured.
     *
     * @return array{applicationId: string, locationId: string, sdkUrl: string, currency: string}|null
     */
    public function publicConfig(): ?array
    {
        if (! $this->isConfigured()) {
            return null;
        }

        return [
            'applicationId' => (string) config('services.square.application_id'),
            'locationId' => $this->locationId(),
            'sdkUrl' => (string) config('services.square.sdk_url'),
            'currency' => $this->currency(),
        ];
    }

    /**
     * Create (and capture) a payment from a Web Payments SDK token.
     *
     * @param  array<string, mixed>  $payload  Must contain `idempotency_key`.
     * @return array<string, mixed> The Square `payment` object.
     *
     * @throws SquarePaymentException
     */
    public function createPayment(array $payload): array
    {
        return $this->send(fn (PendingRequest $request): Response => $request->post('/v2/payments', $payload))['payment'] ?? [];
    }

    /**
     * @return array<string, mixed> The Square `payment` object.
     *
     * @throws SquarePaymentException
     */
    public function getPayment(string $paymentId): array
    {
        return $this->send(fn (PendingRequest $request): Response => $request->get('/v2/payments/'.urlencode($paymentId)))['payment'] ?? [];
    }

    /**
     * List payments created in a time window for the configured location.
     *
     * @return list<array<string, mixed>>
     *
     * @throws SquarePaymentException
     */
    public function listPayments(CarbonInterface $from, CarbonInterface $to): array
    {
        $payments = [];
        $cursor = null;

        for ($page = 0; $page < self::MAX_LIST_PAGES; $page++) {
            $query = array_filter([
                'begin_time' => $from->copy()->utc()->toIso8601String(),
                'end_time' => $to->copy()->utc()->toIso8601String(),
                'sort_order' => 'DESC',
                'limit' => 100,
                'location_id' => $this->locationId(),
                'cursor' => $cursor,
            ]);

            $body = $this->send(fn (PendingRequest $request): Response => $request->get('/v2/payments', $query));

            $payments = array_merge($payments, $body['payments'] ?? []);
            $cursor = $body['cursor'] ?? null;

            if ($cursor === null) {
                break;
            }
        }

        return $payments;
    }

    /**
     * @return array<string, mixed> The Square `refund` object.
     *
     * @throws SquarePaymentException
     */
    public function refundPayment(
        string $paymentId,
        int $amountCents,
        string $currency,
        string $idempotencyKey,
        ?string $reason = null,
    ): array {
        $payload = array_filter([
            'idempotency_key' => $idempotencyKey,
            'payment_id' => $paymentId,
            'amount_money' => ['amount' => $amountCents, 'currency' => $currency],
            'reason' => $reason !== null ? mb_substr($reason, 0, 192) : null,
        ]);

        return $this->send(fn (PendingRequest $request): Response => $request->post('/v2/refunds', $payload))['refund'] ?? [];
    }

    /**
     * @param  callable(PendingRequest): Response  $call
     * @return array<string, mixed>
     *
     * @throws SquarePaymentException
     */
    private function send(callable $call): array
    {
        try {
            $response = $call($this->request());
        } catch (ConnectionException $exception) {
            throw SquarePaymentException::connectionFailed($exception);
        }

        if (! $response->successful()) {
            throw SquarePaymentException::fromResponse($response);
        }

        return $response->json() ?? [];
    }

    private function request(): PendingRequest
    {
        return Http::baseUrl((string) config('services.square.base_url'))
            ->withToken((string) config('services.square.access_token'))
            ->withHeaders(['Square-Version' => (string) config('services.square.api_version')])
            ->acceptJson()
            ->asJson()
            ->connectTimeout(3)
            ->timeout(15)
            ->retry(
                [200, 600],
                0,
                fn (\Throwable $exception): bool => $exception instanceof ConnectionException
                    || ($exception instanceof RequestException
                        && ($exception->response->serverError() || $exception->response->status() === 429)),
                throw: false,
            );
    }
}
