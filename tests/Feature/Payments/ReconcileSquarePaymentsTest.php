<?php

use App\Models\LeadPurchase;
use App\Models\VendingRequest;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Sleep;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed([PermissionSeeder::class, RoleSeeder::class]);
    Sleep::fake();
    Http::preventStrayRequests();
    configureSquare();
});

function pendingPurchaseAged(int $minutes, array $attributes = []): LeadPurchase
{
    return LeadPurchase::factory()->pending()->create([
        'user_id' => makeOperator()->id,
        'leadable_id' => VendingRequest::factory(),
        'created_at' => now()->subMinutes($minutes),
    ] + $attributes);
}

test('a pending purchase is settled from the Square payment list by its reference', function () {
    $purchase = pendingPurchaseAged(10);

    Http::fake(['connect.squareupsandbox.com/v2/payments?*' => Http::response([
        'payments' => [
            squarePaymentObject(null, ['id' => 'OTHER', 'reference_id' => 'LP-9999']),
            squarePaymentObject($purchase),
        ],
    ])]);

    $this->artisan('payments:reconcile')
        ->expectsOutputToContain('1 settled')
        ->assertSuccessful();

    $purchase->refresh();

    expect($purchase->status)->toBe(LeadPurchase::STATUS_PAID)
        ->and($purchase->square_payment_id)->toBe('PAY-'.$purchase->id)
        ->and($purchase->card_last4)->toBe('1111')
        ->and($purchase->paid_at)->not->toBeNull();

    Http::assertSent(fn (Request $request): bool => str_contains($request->url(), 'location_id=LOC123'));
});

test('a purchase with a known payment id is settled via GetPayment', function () {
    $purchase = pendingPurchaseAged(10, ['square_payment_id' => 'PAY-KNOWN', 'square_status' => 'APPROVED']);

    Http::fake(['connect.squareupsandbox.com/v2/payments/PAY-KNOWN' => Http::response([
        'payment' => squarePaymentObject($purchase, ['id' => 'PAY-KNOWN']),
    ])]);

    $this->artisan('payments:reconcile')->assertSuccessful();

    expect($purchase->refresh()->status)->toBe(LeadPurchase::STATUS_PAID);
});

test('a failed payment releases the lead', function () {
    $purchase = pendingPurchaseAged(10, ['square_payment_id' => 'PAY-FAILED']);

    Http::fake(['connect.squareupsandbox.com/v2/payments/PAY-FAILED' => Http::response([
        'payment' => squarePaymentObject($purchase, ['id' => 'PAY-FAILED', 'status' => 'FAILED']),
    ])]);

    $this->artisan('payments:reconcile')->assertSuccessful();

    $purchase->refresh();

    expect($purchase->status)->toBe(LeadPurchase::STATUS_FAILED)
        ->and($purchase->lead_lock)->toBeNull();
});

test('a purchase Square never heard of is released only after the give-up window', function () {
    $recent = pendingPurchaseAged(30);
    $old = pendingPurchaseAged(70);

    Http::fake(['connect.squareupsandbox.com/v2/payments?*' => Http::response(['payments' => []])]);

    $this->artisan('payments:reconcile')
        ->expectsOutputToContain('1 released')
        ->assertSuccessful();

    expect($recent->refresh()->status)->toBe(LeadPurchase::STATUS_PENDING)
        ->and($recent->lead_lock)->not->toBeNull()
        ->and($old->refresh()->status)->toBe(LeadPurchase::STATUS_FAILED)
        ->and($old->lead_lock)->toBeNull()
        ->and($old->failure_code)->toBe('NOT_FOUND_AT_SQUARE');
});

test('purchases that are too young are left alone and Square is not called', function () {
    $purchase = pendingPurchaseAged(2);

    $this->artisan('payments:reconcile')
        ->expectsOutputToContain('No pending lead purchases')
        ->assertSuccessful();

    expect($purchase->refresh()->status)->toBe(LeadPurchase::STATUS_PENDING);
    Http::assertNothingSent();
});

test('a failing Square lookup never releases an old purchase', function () {
    $purchase = pendingPurchaseAged(120);

    Http::fake(['connect.squareupsandbox.com/v2/payments?*' => Http::response([
        'errors' => [['category' => 'API_ERROR', 'code' => 'INTERNAL_SERVER_ERROR']],
    ], 500)]);

    $this->artisan('payments:reconcile')
        ->expectsOutputToContain('1 still pending')
        ->assertSuccessful();

    expect($purchase->refresh()->status)->toBe(LeadPurchase::STATUS_PENDING)
        ->and($purchase->lead_lock)->not->toBeNull();
});

test('legacy and settled purchases are ignored', function () {
    LeadPurchase::factory()->legacy()->create([
        'user_id' => makeOperator()->id,
        'leadable_id' => VendingRequest::factory(),
        'created_at' => now()->subHours(3),
    ]);
    pendingPurchaseAged(10, ['payment_provider' => LeadPurchase::PROVIDER_LEGACY]);

    $this->artisan('payments:reconcile')
        ->expectsOutputToContain('No pending lead purchases')
        ->assertSuccessful();

    Http::assertNothingSent();
});

test('reconcile does nothing when Square is not configured', function () {
    configureSquare(['location_id' => '']);
    pendingPurchaseAged(120);

    $this->artisan('payments:reconcile')
        ->expectsOutputToContain('not configured')
        ->assertSuccessful();

    Http::assertNothingSent();
});

test('the reconcile command is scheduled every five minutes', function () {
    $this->artisan('schedule:list')
        ->expectsOutputToContain('payments:reconcile')
        ->assertSuccessful();
});
