<?php

use App\Models\LeadPurchase;
use App\Models\VendingRequest;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed([PermissionSeeder::class, RoleSeeder::class]);
});

test('billing shows only the operator paid purchases', function () {
    $operator = makeOperator();
    $other = makeOperator();

    $lead = VendingRequest::factory()->create(['business_name' => 'Acme Vending Co']);
    $paid = LeadPurchase::factory()->create([
        'user_id' => $operator->id,
        'leadable_type' => 'vending',
        'leadable_id' => $lead->id,
        'card_brand' => 'VISA',
        'card_last4' => '4242',
        'amount_cents' => 15000,
        'price' => 150,
    ]);

    LeadPurchase::factory()->pending()->create([
        'user_id' => $operator->id,
        'leadable_type' => 'vending',
        'leadable_id' => VendingRequest::factory()->create()->id,
    ]);

    LeadPurchase::factory()->failed()->create([
        'user_id' => $operator->id,
        'leadable_type' => 'vending',
        'leadable_id' => VendingRequest::factory()->create()->id,
    ]);

    LeadPurchase::factory()->create([
        'user_id' => $other->id,
        'leadable_type' => 'vending',
        'leadable_id' => VendingRequest::factory()->create()->id,
    ]);

    $this->actingAs($operator)
        ->get(route('billing.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('operator/billing/index')
            ->where('lastPaidCard.brand', 'VISA')
            ->where('lastPaidCard.last4', '4242')
            ->has('invoices', 1)
            ->where('invoices.0.id', 'LP-'.$paid->id)
            ->where('invoices.0.location', 'Acme Vending Co')
            ->where('invoices.0.status', 'Paid')
            ->where('invoices.0.amount', 150)
            ->where('invoices.0.card_last4', '4242')
            ->missing('paymentMethod')
        );
});

test('billing empty state has no demo card on file', function () {
    $this->actingAs(makeOperator())
        ->get(route('billing.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('operator/billing/index')
            ->where('lastPaidCard', null)
            ->has('invoices', 0)
            ->missing('paymentMethod')
        );
});
