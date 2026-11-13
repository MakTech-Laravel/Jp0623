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
        'monthly_price' => 35,
        'grace_days' => 0,
    ]);
});

test('memberships bill charges due memberships using current settings price', function () {
    fakeSquareHttp([
        'connect.squareupsandbox.com/v2/payments' => function (Request $request) {
            return Http::response(['payment' => squarePaymentObject(null, [
                'id' => 'PAY-'.$request['reference_id'],
                'reference_id' => $request['reference_id'],
                'amount_money' => ['amount' => $request['amount_money']['amount']],
            ])]);
        },
    ]);

    $operator = makeOperator();
    $periodEnd = now()->subHour();
    $membership = OperatorMembership::factory()->active()->create([
        'user_id' => $operator->id,
        'monthly_price' => 29,
        'current_period_end' => $periodEnd,
        'next_billing_at' => $periodEnd,
        'square_customer_id' => 'CUST-DUE-1',
        'square_card_id' => 'CARD-DUE-1',
    ]);

    $this->artisan('memberships:bill')
        ->assertSuccessful();

    $membership->refresh();
    $payment = MembershipPayment::query()->sole();

    expect($payment->status)->toBe(MembershipPayment::STATUS_PAID)
        ->and($payment->price)->toBe(35)
        ->and($payment->amount_cents)->toBe(3500)
        ->and($membership->status)->toBe(OperatorMembership::STATUS_ACTIVE)
        ->and($membership->monthly_price)->toBe(35)
        ->and($membership->current_period_end?->toDateTimeString())
        ->toBe($periodEnd->copy()->addDays(30)->toDateTimeString())
        ->and($membership->next_billing_at->isAfter(now()))->toBeTrue();
});

test('memberships bill marks past due when square charge fails', function () {
    fakeSquareHttp([
        'connect.squareupsandbox.com/v2/payments' => Http::response([
            'errors' => [[
                'category' => 'PAYMENT_METHOD_ERROR',
                'code' => 'CARD_DECLINED',
                'detail' => 'Card declined.',
            ]],
        ], 400),
    ]);

    $operator = makeOperator();
    $membership = OperatorMembership::factory()->active()->create([
        'user_id' => $operator->id,
        'next_billing_at' => now()->subDay(),
        'square_customer_id' => 'CUST-FAIL-1',
        'square_card_id' => 'CARD-FAIL-1',
    ]);

    $this->artisan('memberships:bill')
        ->assertSuccessful();

    expect($membership->fresh()->status)->toBe(OperatorMembership::STATUS_PAST_DUE)
        ->and(MembershipPayment::query()->sole()->status)->toBe(MembershipPayment::STATUS_FAILED);
});

test('memberships bill skips when membership mode is free', function () {
    app(MembershipSettingsService::class)->update([
        'mode' => MembershipSetting::MODE_FREE,
        'monthly_price' => 29,
        'grace_days' => 0,
    ]);

    $operator = makeOperator();
    OperatorMembership::factory()->active()->create([
        'user_id' => $operator->id,
        'next_billing_at' => now()->subDay(),
    ]);

    $this->artisan('memberships:bill')
        ->assertSuccessful();

    expect(MembershipPayment::query()->count())->toBe(0);
});
