<?php

use App\Enums\RoleEnum;
use App\Models\LeadPurchase;
use App\Models\User;
use App\Models\VendingRequest;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed([PermissionSeeder::class, RoleSeeder::class]);

    $this->admin = User::factory()->create();
    $this->admin->assignRole(RoleEnum::SUPER_ADMIN->value);
});

test('guests cannot access admin payments', function () {
    $this->get(route('admin.payments.index'))
        ->assertRedirect(route('login'));
});

test('users without permission are forbidden from admin payments', function () {
    $plain = User::factory()->create();

    $this->actingAs($plain)
        ->get(route('admin.payments.index'))
        ->assertForbidden();
});

test('super admin can view the payments list', function () {
    $operator = makeOperator(['company_name' => 'Ops Co']);
    $lead = VendingRequest::factory()->create(['business_name' => 'Site One']);

    $purchase = LeadPurchase::factory()->create([
        'user_id' => $operator->id,
        'leadable_type' => 'vending',
        'leadable_id' => $lead->id,
        'square_payment_id' => 'sqpay_admin_list_1',
    ]);

    $this->actingAs($this->admin)
        ->get(route('admin.payments.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/payments/index')
            ->has('payments.data', 1)
            ->where('payments.data.0.invoice_id', 'LP-'.$purchase->id)
            ->where('payments.data.0.lead_name', 'Site One')
            ->where('payments.data.0.square_payment_id', 'sqpay_admin_list_1')
            ->has('filters')
        );
});

test('admin payments can be filtered by status and search', function () {
    $operator = makeOperator();
    $paidLead = VendingRequest::factory()->create();
    $failedLead = VendingRequest::factory()->create();

    $paid = LeadPurchase::factory()->create([
        'user_id' => $operator->id,
        'leadable_type' => 'vending',
        'leadable_id' => $paidLead->id,
        'square_payment_id' => 'sqpay_filter_paid',
    ]);

    LeadPurchase::factory()->failed()->create([
        'user_id' => $operator->id,
        'leadable_type' => 'vending',
        'leadable_id' => $failedLead->id,
    ]);

    $this->actingAs($this->admin)
        ->get(route('admin.payments.index', ['status' => 'paid', 'search' => 'LP-'.$paid->id]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/payments/index')
            ->has('payments.data', 1)
            ->where('payments.data.0.invoice_id', 'LP-'.$paid->id)
            ->where('filters.status', 'paid')
        );
});
