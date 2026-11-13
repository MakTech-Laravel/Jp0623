<?php

use App\Models\MembershipSetting;
use App\Models\OperatorMembership;
use App\Models\VendingRequest;
use App\Services\MembershipSettingsService;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed([PermissionSeeder::class, RoleSeeder::class]);
});

function enablePaidMembership(int $monthlyPrice = 29): void
{
    app(MembershipSettingsService::class)->update([
        'mode' => MembershipSetting::MODE_PAID,
        'monthly_price' => $monthlyPrice,
        'grace_days' => 0,
    ]);
}

test('free mode allows lead marketplace without a paid membership', function () {
    $operator = makeOperator();
    OperatorMembership::factory()->create([
        'user_id' => $operator->id,
        'status' => OperatorMembership::STATUS_ACTIVE_FREE,
    ]);

    $this->actingAs($operator)
        ->get(route('leads.index'))
        ->assertOk();
});

test('paid mode blocks lead marketplace when membership is required', function () {
    enablePaidMembership();

    $operator = makeOperator();
    OperatorMembership::factory()->required()->create([
        'user_id' => $operator->id,
    ]);

    $this->actingAs($operator)
        ->get(route('leads.index'))
        ->assertRedirect(route('subscription.index'));
});

test('paid mode allows lead marketplace when membership is active', function () {
    enablePaidMembership();

    $operator = makeOperator();
    OperatorMembership::factory()->active()->create([
        'user_id' => $operator->id,
    ]);

    $this->actingAs($operator)
        ->get(route('leads.index'))
        ->assertOk();
});

test('paid mode blocks lead purchase without active membership', function () {
    enablePaidMembership();
    configureSquare();

    $operator = makeOperator();
    OperatorMembership::factory()->required()->create([
        'user_id' => $operator->id,
    ]);
    $lead = VendingRequest::factory()->create();

    $this->actingAs($operator)
        ->post(route('leads.purchase', ['source' => 'vending', 'lead' => $lead->id]), [
            'source_id' => 'cnon:card-nonce',
        ])
        ->assertRedirect(route('subscription.index'));
});
