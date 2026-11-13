<?php

use App\Models\CoffeeServiceRequest;
use App\Models\LeadPurchase;
use App\Models\VendingRequest;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Sleep;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed([PermissionSeeder::class, RoleSeeder::class]);
    Sleep::fake();
    Http::preventStrayRequests();
    configureSquare();
});

function purchaseUrl(string $source, int $leadId): string
{
    return route('leads.purchase', ['source' => $source, 'lead' => $leadId]);
}

/**
 * Fake the Square CreatePayment endpoint with a COMPLETED payment for any purchase.
 */
function fakeSquareCompleted(): void
{
    fakeSquareHttp([
        'connect.squareupsandbox.com/v2/payments' => function (Request $request) {
            $reference = $request['reference_id'];

            return Http::response(['payment' => squarePaymentObject(null, [
                'id' => 'PAY-'.$reference,
                'reference_id' => $reference,
                'amount_money' => ['amount' => $request['amount_money']['amount']],
            ])]);
        },
    ]);
}

test('checkout charges the lead price instead of the global default', function () {
    fakeSquareCompleted();

    $lead = VendingRequest::factory()->create([
        'business_name' => 'Custom Price Corp',
        'price' => 225,
    ]);
    $operator = makeOperator();

    $this->actingAs($operator)
        ->post(purchaseUrl('vending', $lead->id), ['source_id' => 'cnon:card-nonce'])
        ->assertRedirect(route('leads.purchased'));

    $purchase = LeadPurchase::query()->sole();

    expect($purchase->price)->toBe(225)
        ->and($purchase->amount_cents)->toBe(22500);
});

test('a successful payment unlocks the lead and stores the payment details', function () {
    fakeSquareCompleted();

    $lead = VendingRequest::factory()->create([
        'business_name' => 'Unlocked Corp',
        'address' => '12 Visible St',
        'contact_name' => 'Visible Person',
        'email' => 'visible@example.com',
        'phone' => '555-0199',
    ]);
    $operator = makeOperator();

    $this->actingAs($operator)
        ->post(purchaseUrl('vending', $lead->id), ['source_id' => 'cnon:card-nonce'])
        ->assertRedirect(route('leads.purchased'))
        ->assertSessionHas('inertia.flash_data.toast.type', 'success');

    $purchase = LeadPurchase::query()->sole();

    expect($purchase->user_id)->toBe($operator->id)
        ->and($purchase->status)->toBe(LeadPurchase::STATUS_PAID)
        ->and($purchase->lead_lock)->toBe('vending:'.$lead->id)
        ->and($purchase->amount_cents)->toBe(15000)
        ->and($purchase->price)->toBe(150)
        ->and($purchase->currency)->toBe('USD')
        ->and($purchase->payment_provider)->toBe('square')
        ->and($purchase->square_payment_id)->toBe('PAY-LP-'.$purchase->id)
        ->and($purchase->square_order_id)->toBe('ORDER-1')
        ->and($purchase->square_receipt_url)->toContain('squareup.com/receipt')
        ->and($purchase->card_brand)->toBe('VISA')
        ->and($purchase->card_last4)->toBe('1111')
        ->and($purchase->paid_at)->not->toBeNull()
        ->and($purchase->purchased_at)->not->toBeNull()
        ->and($purchase->idempotency_key)->not->toBeNull()
        ->and(strlen($purchase->idempotency_key))->toBeLessThanOrEqual(45)
        ->and($purchase->square_payload['card_details']['card'])->not->toHaveKey('fingerprint');

    expect(squareCallCount())->toBe(1);
    Http::assertSent(fn (Request $request): bool => $request->url() === 'https://connect.squareupsandbox.com/v2/payments'
        && $request->hasHeader('Authorization', 'Bearer test-access-token')
        && $request->hasHeader('Square-Version', '2026-09-16')
        && $request['source_id'] === 'cnon:card-nonce'
        && $request['idempotency_key'] === $purchase->idempotency_key
        && $request['amount_money'] === ['amount' => 15000, 'currency' => 'USD']
        && $request['location_id'] === 'LOC123'
        && $request['reference_id'] === 'LP-'.$purchase->id
        && $request['buyer_email_address'] === $operator->email
        && $request['autocomplete'] === true);

    $this->actingAs($operator)
        ->get(route('leads.purchased'))
        ->assertInertia(fn (Assert $page) => $page
            ->has('purchasedLeads', 1)
            ->where('purchasedLeads.0.id', 'V-'.$lead->id)
            ->where('purchasedLeads.0.payment_status', 'paid')
            ->where('purchasedLeads.0.name', 'Unlocked Corp')
            ->where('purchasedLeads.0.contact_name', 'Visible Person')
            ->where('purchasedLeads.0.contact_phone', '555-0199')
            ->where('purchasedLeads.0.email', 'visible@example.com')
            ->where('purchasedLeads.0.card_last4', '1111')
            ->where('purchasedLeads.0.receipt_url', fn ($url): bool => str_contains((string) $url, 'receipt'))
        );
});

test('coffee leads can be bought too', function () {
    fakeSquareCompleted();

    $lead = CoffeeServiceRequest::factory()->create();

    $this->actingAs(makeOperator())
        ->post(purchaseUrl('coffee', $lead->id), ['source_id' => 'cnon:card-nonce'])
        ->assertRedirect(route('leads.purchased'));

    expect(LeadPurchase::query()->sole()->lead_lock)->toBe('coffee:'.$lead->id);
});

test('a lead bought by one operator cannot be bought or seen by another', function () {
    fakeSquareCompleted();

    $lead = VendingRequest::factory()->create();
    $first = makeOperator();
    $second = makeOperator();

    $this->actingAs($first)
        ->post(purchaseUrl('vending', $lead->id), ['source_id' => 'cnon:first'])
        ->assertRedirect(route('leads.purchased'));

    $this->actingAs($second)
        ->get(route('leads.index'))
        ->assertInertia(fn (Assert $page) => $page->has('leads', 0));

    $this->actingAs($second)
        ->from(route('leads.index'))
        ->post(purchaseUrl('vending', $lead->id), ['source_id' => 'cnon:second'])
        ->assertSessionHasErrors(['payment' => 'This lead was just purchased by another operator.']);

    expect(squareCallCount())->toBe(1);
    expect(LeadPurchase::query()->count())->toBe(1);
});

test('the database refuses two purchases that hold the same lead', function () {
    $lead = VendingRequest::factory()->create();

    LeadPurchase::factory()->create(['leadable_id' => $lead->id]);
    LeadPurchase::factory()->pending()->create(['leadable_id' => $lead->id]);
})->throws(UniqueConstraintViolationException::class);

test('an unknown outcome keeps the purchase pending and the lead locked', function () {
    fakeSquareHttp(['connect.squareupsandbox.com/*' => fn () => throw new ConnectionException('timed out')]);

    $lead = VendingRequest::factory()->create(['contact_name' => 'Hidden Person', 'email' => 'hidden@example.com']);
    $operator = makeOperator();

    $this->actingAs($operator)
        ->post(purchaseUrl('vending', $lead->id), ['source_id' => 'cnon:card-nonce'])
        ->assertRedirect(route('leads.purchased'))
        ->assertSessionHas('inertia.flash_data.toast.type', 'warning');

    $purchase = LeadPurchase::query()->sole();

    expect($purchase->status)->toBe(LeadPurchase::STATUS_PENDING)
        ->and($purchase->lead_lock)->toBe('vending:'.$lead->id)
        ->and($purchase->paid_at)->toBeNull();

    $this->actingAs(makeOperator())
        ->get(route('leads.index'))
        ->assertInertia(fn (Assert $page) => $page->has('leads', 0));

    $response = $this->actingAs($operator)->get(route('leads.purchased'));

    $response->assertInertia(fn (Assert $page) => $page
        ->has('purchasedLeads', 1)
        ->where('purchasedLeads.0.payment_status', 'pending')
        ->where('purchasedLeads.0.contact_name', null)
        ->where('purchasedLeads.0.email', null)
        ->where('purchasedLeads.0.name', null)
    );

    $body = json_encode($response->viewData('page')['props']);

    expect($body)->not->toContain('Hidden Person')->and($body)->not->toContain('hidden@example.com');
});

test('a declined card fails the purchase and releases the lead', function () {
    fakeSquareHttp(['connect.squareupsandbox.com/v2/payments' => Http::response([
        'errors' => [['category' => 'PAYMENT_METHOD_ERROR', 'code' => 'GENERIC_DECLINE', 'detail' => 'Declined by issuer']],
    ], 402)]);

    $lead = VendingRequest::factory()->create();
    $operator = makeOperator();

    $this->actingAs($operator)
        ->from(route('leads.index'))
        ->post(purchaseUrl('vending', $lead->id), ['source_id' => 'cnon:card-nonce'])
        ->assertSessionHasErrors(['payment' => 'Your card was declined.']);

    $purchase = LeadPurchase::query()->sole();

    expect($purchase->status)->toBe(LeadPurchase::STATUS_FAILED)
        ->and($purchase->lead_lock)->toBeNull()
        ->and($purchase->failure_code)->toBe('GENERIC_DECLINE')
        ->and($purchase->failure_category)->toBe('PAYMENT_METHOD_ERROR')
        ->and($purchase->failure_message)->toBe('Declined by issuer')
        ->and($purchase->paid_at)->toBeNull();

    $this->actingAs($operator)
        ->get(route('leads.index'))
        ->assertInertia(fn (Assert $page) => $page->has('leads', 1));

    $this->actingAs($operator)
        ->get(route('leads.purchased'))
        ->assertInertia(fn (Assert $page) => $page->has('purchasedLeads', 0));
});

test('after a failed attempt the same lead can be bought by someone else', function () {
    $lead = VendingRequest::factory()->create();
    $first = makeOperator();
    $second = makeOperator();

    $attempts = 0;

    fakeSquareHttp(['connect.squareupsandbox.com/v2/payments' => function (Request $request) use (&$attempts) {
        if (++$attempts === 1) {
            return Http::response([
                'errors' => [['category' => 'PAYMENT_METHOD_ERROR', 'code' => 'CARD_EXPIRED']],
            ], 402);
        }

        return Http::response(['payment' => squarePaymentObject(null, [
            'reference_id' => $request['reference_id'],
            'amount_money' => ['amount' => $request['amount_money']['amount']],
        ])]);
    }]);

    $this->actingAs($first)
        ->post(purchaseUrl('vending', $lead->id), ['source_id' => 'cnon:first'])
        ->assertSessionHasErrors('payment');

    $this->actingAs($second)
        ->post(purchaseUrl('vending', $lead->id), ['source_id' => 'cnon:second'])
        ->assertRedirect(route('leads.purchased'));

    expect(LeadPurchase::query()->where('user_id', $second->id)->sole()->isPaid())->toBeTrue()
        ->and(LeadPurchase::query()->where('user_id', $first->id)->sole()->status)->toBe(LeadPurchase::STATUS_FAILED);
});

test('a configuration error fails the purchase with a generic message', function () {
    fakeSquareHttp(['connect.squareupsandbox.com/v2/payments' => Http::response([
        'errors' => [['category' => 'AUTHENTICATION_ERROR', 'code' => 'UNAUTHORIZED']],
    ], 401)]);

    $lead = VendingRequest::factory()->create();

    $this->actingAs(makeOperator())
        ->post(purchaseUrl('vending', $lead->id), ['source_id' => 'cnon:card-nonce'])
        ->assertSessionHasErrors(['payment' => 'Payments are temporarily unavailable. Please try again later.']);

    expect(LeadPurchase::query()->sole()->status)->toBe(LeadPurchase::STATUS_FAILED);
});

test('payments are refused cleanly when Square is not configured', function () {
    configureSquare(['location_id' => '']);

    $lead = VendingRequest::factory()->create();

    $this->actingAs(makeOperator())
        ->post(purchaseUrl('vending', $lead->id), ['source_id' => 'cnon:card-nonce'])
        ->assertSessionHasErrors(['payment' => 'Payments are temporarily unavailable. Please try again later.']);

    expect(LeadPurchase::query()->count())->toBe(0);
    Http::assertNothingSent();
});

test('submitting twice never charges the same operator twice', function () {
    fakeSquareCompleted();

    $lead = VendingRequest::factory()->create();
    $operator = makeOperator();

    $this->actingAs($operator)->post(purchaseUrl('vending', $lead->id), ['source_id' => 'cnon:one'])->assertRedirect();
    $this->actingAs($operator)->post(purchaseUrl('vending', $lead->id), ['source_id' => 'cnon:two'])->assertRedirect();

    expect(squareCallCount())->toBe(1);
    expect(LeadPurchase::query()->count())->toBe(1);
});

test('a payment that does not match the purchase never unlocks the lead', function () {
    fakeSquareHttp(['connect.squareupsandbox.com/v2/payments' => function (Request $request) {
        return Http::response(['payment' => squarePaymentObject(null, [
            'reference_id' => $request['reference_id'],
            'amount_money' => ['amount' => 100],
        ])]);
    }]);

    $lead = VendingRequest::factory()->create();

    $this->actingAs(makeOperator())
        ->post(purchaseUrl('vending', $lead->id), ['source_id' => 'cnon:card-nonce'])
        ->assertRedirect(route('leads.purchased'))
        ->assertSessionHas('inertia.flash_data.toast.type', 'warning');

    $purchase = LeadPurchase::query()->sole();

    expect($purchase->status)->toBe(LeadPurchase::STATUS_PENDING)
        ->and($purchase->lead_lock)->not->toBeNull()
        ->and($purchase->paid_at)->toBeNull();
});

test('closed and unknown leads return 404 and make no charge', function () {
    $closed = CoffeeServiceRequest::factory()->create(['status' => 'closed']);
    $operator = makeOperator();

    $this->actingAs($operator)
        ->post(purchaseUrl('coffee', $closed->id), ['source_id' => 'cnon:card-nonce'])
        ->assertNotFound();

    $this->actingAs($operator)
        ->post(purchaseUrl('vending', 9999), ['source_id' => 'cnon:card-nonce'])
        ->assertNotFound();

    $this->actingAs($operator)
        ->post('/leads/other/1/purchase', ['source_id' => 'cnon:card-nonce'])
        ->assertNotFound();

    expect(LeadPurchase::query()->count())->toBe(0);
    Http::assertNothingSent();
});

test('the card token is required and guests are redirected to log in', function () {
    $lead = VendingRequest::factory()->create();

    $this->actingAs(makeOperator())
        ->post(purchaseUrl('vending', $lead->id), [])
        ->assertSessionHasErrors('source_id');

    auth()->logout();

    $this->post(purchaseUrl('vending', $lead->id), ['source_id' => 'cnon:card-nonce'])
        ->assertRedirect(route('login'));

    expect(LeadPurchase::query()->count())->toBe(0);
    Http::assertNothingSent();
});
