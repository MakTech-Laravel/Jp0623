<?php

use App\Exceptions\Payments\PaymentFailedException;
use App\Exceptions\Payments\SquareNotConfiguredException;
use App\Exceptions\Payments\SquarePaymentException;
use App\Services\Payments\SquareClient;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Sleep;

beforeEach(function () {
    Sleep::fake();
    Http::preventStrayRequests();

    config()->set('services.square', [
        'environment' => 'sandbox',
        'application_id' => 'sandbox-sq0idb-test',
        'access_token' => 'test-access-token',
        'location_id' => 'LOC123',
        'webhook_signature_key' => 'test-signature-key',
        'webhook_url' => 'https://example.test/webhooks/square',
        'currency' => 'USD',
        'api_version' => '2026-09-16',
        'base_url' => 'https://connect.squareupsandbox.com',
        'sdk_url' => 'https://sandbox.web.squarecdn.com/v1/square.js',
    ]);
});

function squarePayment(array $overrides = []): array
{
    return ['payment' => array_replace_recursive([
        'id' => 'PAY1',
        'status' => 'COMPLETED',
        'amount_money' => ['amount' => 15000, 'currency' => 'USD'],
        'location_id' => 'LOC123',
        'reference_id' => 'LP-1',
    ], $overrides)];
}

test('createPayment posts the payload with auth and version headers', function () {
    Http::fake(['connect.squareupsandbox.com/v2/payments' => Http::response(squarePayment())]);

    $payment = app(SquareClient::class)->createPayment([
        'idempotency_key' => 'key-1',
        'source_id' => 'cnon:card-token',
        'amount_money' => ['amount' => 15000, 'currency' => 'USD'],
    ]);

    expect($payment['id'])->toBe('PAY1')->and($payment['status'])->toBe('COMPLETED');

    Http::assertSent(fn (Request $request): bool => $request->url() === 'https://connect.squareupsandbox.com/v2/payments'
        && $request->method() === 'POST'
        && $request->hasHeader('Authorization', 'Bearer test-access-token')
        && $request->hasHeader('Square-Version', '2026-09-16')
        && $request['idempotency_key'] === 'key-1'
        && $request['source_id'] === 'cnon:card-token');
});

test('a transient server error is retried with the same idempotency key', function () {
    Http::fake(['connect.squareupsandbox.com/v2/payments' => Http::sequence()
        ->push(['errors' => [['category' => 'API_ERROR', 'code' => 'INTERNAL_SERVER_ERROR']]], 500)
        ->push(squarePayment())]);

    $payment = app(SquareClient::class)->createPayment([
        'idempotency_key' => 'stable-key',
        'source_id' => 'cnon:card-token',
    ]);

    expect($payment['id'])->toBe('PAY1');

    $keys = [];
    Http::assertSentCount(2);
    Http::assertSent(function (Request $request) use (&$keys): bool {
        $keys[] = $request['idempotency_key'];

        return true;
    });
    expect(array_unique($keys))->toBe(['stable-key']);
});

test('a declined card throws a definitive error', function () {
    Http::fake(['connect.squareupsandbox.com/v2/payments' => Http::response([
        'errors' => [['category' => 'PAYMENT_METHOD_ERROR', 'code' => 'GENERIC_DECLINE', 'detail' => 'Declined']],
    ], 402)]);

    try {
        app(SquareClient::class)->createPayment(['idempotency_key' => 'k', 'source_id' => 's']);
        $this->fail('Expected SquarePaymentException');
    } catch (SquarePaymentException $exception) {
        expect($exception->errorCode)->toBe('GENERIC_DECLINE')
            ->and($exception->category)->toBe('PAYMENT_METHOD_ERROR')
            ->and($exception->isOutcomeUnknown())->toBeFalse()
            ->and($exception->getMessage())->not->toContain('test-access-token')
            ->and(PaymentFailedException::fromSquare($exception)->getMessage())->toBe('Your card was declined.');
    }

    Http::assertSentCount(1);
});

test('persistent server errors leave the outcome unknown', function () {
    Http::fake(['connect.squareupsandbox.com/v2/payments' => Http::response([
        'errors' => [['category' => 'API_ERROR', 'code' => 'INTERNAL_SERVER_ERROR']],
    ], 500)]);

    try {
        app(SquareClient::class)->createPayment(['idempotency_key' => 'k', 'source_id' => 's']);
        $this->fail('Expected SquarePaymentException');
    } catch (SquarePaymentException $exception) {
        expect($exception->isOutcomeUnknown())->toBeTrue();
    }
});

test('connection failures leave the outcome unknown', function () {
    Http::fake(['connect.squareupsandbox.com/*' => fn () => throw new ConnectionException('timeout')]);

    try {
        app(SquareClient::class)->createPayment(['idempotency_key' => 'k', 'source_id' => 's']);
        $this->fail('Expected SquarePaymentException');
    } catch (SquarePaymentException $exception) {
        expect($exception->connectionFailed)->toBeTrue()
            ->and($exception->isOutcomeUnknown())->toBeTrue();
    }
});

test('authentication errors are configuration problems with a generic user message', function () {
    Http::fake(['connect.squareupsandbox.com/v2/payments' => Http::response([
        'errors' => [['category' => 'AUTHENTICATION_ERROR', 'code' => 'UNAUTHORIZED']],
    ], 401)]);

    try {
        app(SquareClient::class)->createPayment(['idempotency_key' => 'k', 'source_id' => 's']);
        $this->fail('Expected SquarePaymentException');
    } catch (SquarePaymentException $exception) {
        expect($exception->isConfigurationProblem())->toBeTrue()
            ->and($exception->isOutcomeUnknown())->toBeFalse()
            ->and(PaymentFailedException::fromSquare($exception)->getMessage())
            ->toBe('Payments are temporarily unavailable. Please try again later.');
    }
});

test('getPayment fetches a single payment', function () {
    Http::fake(['connect.squareupsandbox.com/v2/payments/PAY1' => Http::response(squarePayment())]);

    expect(app(SquareClient::class)->getPayment('PAY1')['reference_id'])->toBe('LP-1');
});

test('listPayments follows the cursor and scopes to the location', function () {
    Http::fake(['connect.squareupsandbox.com/v2/payments*' => Http::sequence()
        ->push(['payments' => [['id' => 'A']], 'cursor' => 'next'])
        ->push(['payments' => [['id' => 'B']]])]);

    $payments = app(SquareClient::class)->listPayments(Carbon::now()->subHour(), Carbon::now());

    expect(collect($payments)->pluck('id')->all())->toBe(['A', 'B']);

    Http::assertSent(fn (Request $request): bool => str_contains($request->url(), 'location_id=LOC123')
        && str_contains($request->url(), 'begin_time='));
});

test('refundPayment sends the idempotency key and amount', function () {
    Http::fake(['connect.squareupsandbox.com/v2/refunds' => Http::response([
        'refund' => ['id' => 'REF1', 'status' => 'PENDING'],
    ])]);

    $refund = app(SquareClient::class)->refundPayment('PAY1', 15000, 'USD', 'refund-key', 'Duplicate lead');

    expect($refund['id'])->toBe('REF1');

    Http::assertSent(fn (Request $request): bool => $request['payment_id'] === 'PAY1'
        && $request['idempotency_key'] === 'refund-key'
        && $request['amount_money'] === ['amount' => 15000, 'currency' => 'USD']
        && $request['reason'] === 'Duplicate lead');
});

test('assertConfigured throws when the location id is missing', function () {
    config()->set('services.square.location_id', '');

    $client = app(SquareClient::class);

    expect($client->isConfigured())->toBeFalse();
    $client->assertConfigured();
})->throws(SquareNotConfiguredException::class);

test('card failure codes map to friendly messages', function (string $code, string $message) {
    expect(PaymentFailedException::fromCode($code)->getMessage())->toBe($message);
})->with([
    ['INSUFFICIENT_FUNDS', 'Your card has insufficient funds.'],
    ['CARD_EXPIRED', 'Your card has expired.'],
    ['CVV_FAILURE', 'The security code is incorrect.'],
    ['ADDRESS_VERIFICATION_FAILURE', 'The postal code does not match.'],
    ['SOMETHING_ELSE', 'Payment could not be completed. You were not charged.'],
]);
