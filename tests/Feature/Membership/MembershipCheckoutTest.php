<?php

use App\Models\MembershipPayment;
use App\Models\MembershipSetting;
use App\Models\OperatorMembership;
use App\Services\MembershipSettingsService;
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

    app(MembershipSettingsService::class)->update([
        'mode' => MembershipSetting::MODE_PAID,
        'monthly_price' => 29,
        'grace_days' => 0,
    ]);
});

function fakeMembershipSquareCheckout(): void
{
    fakeSquareHttp([
        'connect.squareupsandbox.com/v2/customers' => Http::response([
            'customer' => ['id' => 'CUST-MEM-1'],
        ]),
        'connect.squareupsandbox.com/v2/cards' => Http::response([
            'card' => [
                'id' => 'CARD-MEM-1',
                'card_brand' => 'VISA',
                'last_4' => '4242',
            ],
        ]),
        'connect.squareupsandbox.com/v2/payments' => function (Request $request) {
            $reference = $request['reference_id'];

            return Http::response(['payment' => squarePaymentObject(null, [
                'id' => 'PAY-'.$reference,
                'reference_id' => $reference,
                'amount_money' => ['amount' => $request['amount_money']['amount']],
                'card_details' => [
                    'status' => 'CAPTURED',
                    'card' => ['card_brand' => 'VISA', 'last_4' => '4242'],
                ],
            ])]);
        },
    ]);
}

test('membership checkout creates payment and activates membership', function () {
    fakeMembershipSquareCheckout();

    $operator = makeOperator();
    OperatorMembership::factory()->required()->create([
        'user_id' => $operator->id,
    ]);

    $this->actingAs($operator)
        ->post(route('subscription.checkout'), ['source_id' => 'cnon:card-nonce'])
        ->assertRedirect(route('subscription.index'));

    $membership = $operator->membership()->first();
    $payment = MembershipPayment::query()->sole();

    expect($membership->status)->toBe(OperatorMembership::STATUS_ACTIVE)
        ->and($membership->square_customer_id)->toBe('CUST-MEM-1')
        ->and($membership->square_card_id)->toBe('CARD-MEM-1')
        ->and($membership->card_last4)->toBe('4242')
        ->and($membership->next_billing_at)->not->toBeNull()
        ->and($membership->current_period_end?->toDateTimeString())
        ->toBe($membership->current_period_start?->copy()->addDays(30)->toDateTimeString())
        ->and($payment->status)->toBe(MembershipPayment::STATUS_PAID)
        ->and($payment->amount_cents)->toBe(2900)
        ->and($payment->price)->toBe(29)
        ->and($payment->square_payment_id)->toBe('PAY-MB-'.$payment->id);

    Http::assertSent(fn (Request $request): bool => str_contains($request->url(), '/v2/payments')
        && $request['source_id'] === 'CARD-MEM-1'
        && $request['customer_id'] === 'CUST-MEM-1'
        && $request['amount_money'] === ['amount' => 2900, 'currency' => 'USD']);
});

test('membership checkout is rejected while membership is free', function () {
    app(MembershipSettingsService::class)->update([
        'mode' => MembershipSetting::MODE_FREE,
        'monthly_price' => 29,
        'grace_days' => 0,
    ]);

    $operator = makeOperator();
    OperatorMembership::factory()->create([
        'user_id' => $operator->id,
        'status' => OperatorMembership::STATUS_ACTIVE_FREE,
    ]);

    $this->actingAs($operator)
        ->from(route('subscription.index'))
        ->post(route('subscription.checkout'), ['source_id' => 'cnon:card-nonce'])
        ->assertRedirect(route('subscription.index'))
        ->assertSessionHasErrors('source_id');

    expect(MembershipPayment::query()->count())->toBe(0);
});
