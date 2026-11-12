<?php

use App\Models\LeadPurchase;
use App\Models\PaymentEvent;
use App\Models\VendingRequest;
use App\Services\LeadPurchaseService;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed([PermissionSeeder::class, RoleSeeder::class]);
    configureSquare();
});

/**
 * @param  array<string, mixed>  $payment
 * @return array<string, mixed>
 */
function squareWebhookPayload(array $payment, string $eventId = 'evt-1', string $type = 'payment.updated'): array
{
    return [
        'merchant_id' => 'MERCHANT1',
        'type' => $type,
        'event_id' => $eventId,
        'created_at' => '2026-11-12T00:00:00Z',
        'data' => [
            'type' => 'payment',
            'id' => $payment['id'],
            'object' => ['payment' => $payment],
        ],
    ];
}

/**
 * POST a Square webhook with the raw body signed like Square does.
 *
 * @param  array<string, mixed>  $payload
 */
function postSquareWebhook(mixed $test, array $payload, ?string $signature = null, bool $sign = true): TestResponse
{
    $body = json_encode($payload);

    $signature ??= $sign
        ? base64_encode(hash_hmac('sha256', config('services.square.webhook_url').$body, config('services.square.webhook_signature_key'), true))
        : null;

    $server = ['CONTENT_TYPE' => 'application/json'];

    if ($signature !== null) {
        $server['HTTP_X_SQUARE_HMACSHA256_SIGNATURE'] = $signature;
    }

    return $test->call('POST', '/webhooks/square', [], [], [], $server, $body);
}

function pendingPurchase(): LeadPurchase
{
    $lead = VendingRequest::factory()->create();

    return LeadPurchase::factory()->pending()->create([
        'user_id' => makeOperator()->id,
        'leadable_id' => $lead->id,
    ]);
}

test('a signed payment.updated COMPLETED event settles a pending purchase', function () {
    $purchase = pendingPurchase();

    postSquareWebhook($this, squareWebhookPayload(squarePaymentObject($purchase)))
        ->assertOk();

    $purchase->refresh();

    expect($purchase->status)->toBe(LeadPurchase::STATUS_PAID)
        ->and($purchase->square_payment_id)->toBe('PAY-'.$purchase->id)
        ->and($purchase->square_receipt_url)->toContain('squareup.com')
        ->and($purchase->card_last4)->toBe('1111')
        ->and($purchase->paid_at)->not->toBeNull()
        ->and($purchase->lead_lock)->not->toBeNull();

    $event = PaymentEvent::query()->sole();

    expect($event->event_id)->toBe('evt-1')
        ->and($event->outcome)->toBe(PaymentEvent::OUTCOME_APPLIED)
        ->and($event->processed_at)->not->toBeNull();

    $this->actingAs($purchase->user)
        ->get(route('leads.purchased'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('purchasedLeads.0.payment_status', 'paid')
            ->where('purchasedLeads.0.contact_name', $purchase->leadable->contact_name)
        );
});

test('requests with a bad or missing signature are rejected and change nothing', function () {
    $purchase = pendingPurchase();
    $payload = squareWebhookPayload(squarePaymentObject($purchase));

    postSquareWebhook($this, $payload, 'not-the-signature')->assertForbidden();
    postSquareWebhook($this, $payload, sign: false)->assertForbidden();

    expect($purchase->refresh()->status)->toBe(LeadPurchase::STATUS_PENDING)
        ->and(PaymentEvent::query()->count())->toBe(0);
});

test('webhooks are refused when no signature key is configured', function () {
    configureSquare(['webhook_signature_key' => '']);

    $purchase = pendingPurchase();

    postSquareWebhook($this, squareWebhookPayload(squarePaymentObject($purchase)), 'anything')
        ->assertForbidden();

    expect($purchase->refresh()->status)->toBe(LeadPurchase::STATUS_PENDING);
});

test('a duplicate event id is acknowledged but processed only once', function () {
    $purchase = pendingPurchase();
    $payload = squareWebhookPayload(squarePaymentObject($purchase));

    postSquareWebhook($this, $payload)->assertOk();
    $firstPaidAt = $purchase->refresh()->paid_at;

    $this->travel(5)->minutes();

    postSquareWebhook($this, $payload)->assertOk();

    expect(PaymentEvent::query()->count())->toBe(1)
        ->and(LeadPurchase::query()->paid()->count())->toBe(1)
        ->and($purchase->refresh()->paid_at->equalTo($firstPaidAt))->toBeTrue();
});

test('a payment we cannot match is acknowledged and logged as unmatched', function () {
    postSquareWebhook($this, squareWebhookPayload(squarePaymentObject(null, [
        'id' => 'PAY-UNKNOWN',
        'reference_id' => 'SOMETHING-ELSE',
    ])))->assertOk();

    expect(PaymentEvent::query()->sole()->outcome)->toBe(PaymentEvent::OUTCOME_UNMATCHED)
        ->and(LeadPurchase::query()->count())->toBe(0);
});

test('a payment with the wrong amount never unlocks the lead', function () {
    $purchase = pendingPurchase();

    postSquareWebhook($this, squareWebhookPayload(squarePaymentObject($purchase, [
        'amount_money' => ['amount' => 100],
    ])))->assertOk();

    $purchase->refresh();

    expect($purchase->status)->toBe(LeadPurchase::STATUS_PENDING)
        ->and($purchase->lead_lock)->not->toBeNull()
        ->and($purchase->paid_at)->toBeNull()
        ->and(PaymentEvent::query()->sole()->outcome)->toBe(PaymentEvent::OUTCOME_MISMATCH);
});

test('a payment for another currency or location never unlocks the lead', function (array $override) {
    $purchase = pendingPurchase();

    postSquareWebhook($this, squareWebhookPayload(squarePaymentObject($purchase, $override)))->assertOk();

    expect($purchase->refresh()->status)->toBe(LeadPurchase::STATUS_PENDING);
})->with([
    'currency' => [['amount_money' => ['currency' => 'EUR']]],
    'location' => [['location_id' => 'OTHER-LOCATION']],
]);

test('FAILED and CANCELED payments release the lead', function (string $status) {
    $purchase = pendingPurchase();

    postSquareWebhook($this, squareWebhookPayload(squarePaymentObject($purchase, ['status' => $status])))
        ->assertOk();

    $purchase->refresh();

    expect($purchase->status)->toBe(LeadPurchase::STATUS_FAILED)
        ->and($purchase->lead_lock)->toBeNull()
        ->and($purchase->failure_code)->toBe('PAYMENT_'.$status);

    $this->actingAs(makeOperator())
        ->get(route('leads.index'))
        ->assertInertia(fn (Assert $page) => $page->has('leads', 1));
})->with(['FAILED', 'CANCELED']);

test('non-final payment statuses only record the Square ids', function () {
    $purchase = pendingPurchase();

    postSquareWebhook($this, squareWebhookPayload(squarePaymentObject($purchase, ['status' => 'APPROVED'])))
        ->assertOk();

    $purchase->refresh();

    expect($purchase->status)->toBe(LeadPurchase::STATUS_PENDING)
        ->and($purchase->square_status)->toBe('APPROVED')
        ->and($purchase->square_payment_id)->toBe('PAY-'.$purchase->id)
        ->and(PaymentEvent::query()->sole()->outcome)->toBe(PaymentEvent::OUTCOME_IGNORED);
});

test('a late webhook cannot change a purchase that is already settled', function () {
    $paid = LeadPurchase::factory()->create([
        'user_id' => makeOperator()->id,
        'leadable_id' => VendingRequest::factory(),
    ]);

    postSquareWebhook($this, squareWebhookPayload(squarePaymentObject($paid, [
        'id' => $paid->square_payment_id,
        'status' => 'FAILED',
    ])))->assertOk();

    expect($paid->refresh()->status)->toBe(LeadPurchase::STATUS_PAID)
        ->and($paid->lead_lock)->not->toBeNull()
        ->and(PaymentEvent::query()->sole()->outcome)->toBe(PaymentEvent::OUTCOME_IGNORED);
});

test('applying the same payment twice leaves a single paid purchase', function () {
    $purchase = pendingPurchase();
    $payment = squarePaymentObject($purchase);

    $service = app(LeadPurchaseService::class);
    $service->applySquarePayment($purchase, $payment);
    $service->applySquarePayment($purchase, $payment);

    postSquareWebhook($this, squareWebhookPayload($payment))->assertOk();

    expect(LeadPurchase::query()->paid()->count())->toBe(1);
});

test('other event types are stored and ignored', function () {
    postSquareWebhook($this, [
        'merchant_id' => 'MERCHANT1',
        'type' => 'customer.created',
        'event_id' => 'evt-customer',
        'data' => ['type' => 'customer', 'id' => 'CUST1', 'object' => []],
    ])->assertOk();

    expect(PaymentEvent::query()->sole()->outcome)->toBe(PaymentEvent::OUTCOME_IGNORED);
});

test('a signed but malformed payload is rejected', function () {
    postSquareWebhook($this, ['hello' => 'world'])->assertStatus(400);

    expect(PaymentEvent::query()->count())->toBe(0);
});
