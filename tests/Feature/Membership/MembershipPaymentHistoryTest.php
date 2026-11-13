<?php

use App\Enums\RoleEnum;
use App\Models\LeadPurchase;
use App\Models\MembershipPayment;
use App\Models\MembershipSetting;
use App\Models\OperatorMembership;
use App\Models\User;
use App\Models\VendingRequest;
use App\Services\MembershipSettingsService;
use App\Support\AdminDashboardData;
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

test('operator billing includes lead and membership invoices', function () {
    $operator = makeOperator();
    $lead = VendingRequest::factory()->create(['business_name' => 'Lead Site']);
    $membership = OperatorMembership::factory()->active()->create([
        'user_id' => $operator->id,
    ]);

    $leadPurchase = LeadPurchase::factory()->create([
        'user_id' => $operator->id,
        'leadable_type' => 'vending',
        'leadable_id' => $lead->id,
        'amount_cents' => 15000,
        'price' => 150,
        'paid_at' => now()->subHour(),
    ]);

    $membershipPayment = MembershipPayment::factory()->create([
        'user_id' => $operator->id,
        'operator_membership_id' => $membership->id,
        'amount_cents' => 2900,
        'price' => 29,
        'paid_at' => now(),
    ]);

    $this->actingAs($operator)
        ->get(route('billing.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('operator/billing/index')
            ->has('invoices', 2)
            ->where('invoices.0.id', 'MB-'.$membershipPayment->id)
            ->where('invoices.1.id', 'LP-'.$leadPurchase->id)
        );
});

test('admin payments can be filtered by source', function () {
    $operator = makeOperator();
    $lead = VendingRequest::factory()->create();
    $membership = OperatorMembership::factory()->active()->create([
        'user_id' => $operator->id,
    ]);

    LeadPurchase::factory()->create([
        'user_id' => $operator->id,
        'leadable_type' => 'vending',
        'leadable_id' => $lead->id,
    ]);

    $membershipPayment = MembershipPayment::factory()->create([
        'user_id' => $operator->id,
        'operator_membership_id' => $membership->id,
    ]);

    $this->actingAs($this->admin)
        ->get(route('admin.payments.index', ['source' => 'membership']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/payments/index')
            ->has('payments.data', 1)
            ->where('payments.data.0.invoice_id', 'MB-'.$membershipPayment->id)
            ->where('payments.data.0.source', 'membership')
            ->where('filters.source', 'membership')
        );
});

test('admin dashboard revenue splits lead and membership totals', function () {
    $operator = makeOperator();
    $lead = VendingRequest::factory()->create();
    $membership = OperatorMembership::factory()->active()->create([
        'user_id' => $operator->id,
    ]);

    LeadPurchase::factory()->create([
        'user_id' => $operator->id,
        'leadable_type' => 'vending',
        'leadable_id' => $lead->id,
        'amount_cents' => 15000,
        'price' => 150,
        'paid_at' => now(),
    ]);

    MembershipPayment::factory()->create([
        'user_id' => $operator->id,
        'operator_membership_id' => $membership->id,
        'amount_cents' => 2900,
        'price' => 29,
        'paid_at' => now(),
    ]);

    app(MembershipSettingsService::class)->update([
        'mode' => MembershipSetting::MODE_PAID,
        'monthly_price' => 29,
        'grace_days' => 0,
    ]);

    OperatorMembership::factory()->required()->create([
        'user_id' => makeOperator()->id,
    ]);

    $data = AdminDashboardData::make();

    expect($data['revenue']['lead_mtd'])->toBe(150.0)
        ->and($data['revenue']['membership_mtd'])->toBe(29.0)
        ->and($data['revenue']['total_mtd'])->toBe(179.0)
        ->and($data['membership']['mode'])->toBe('paid')
        ->and($data['membership']['required'])->toBe(1);
});

test('register page receives dynamic membership copy props', function () {
    app(MembershipSettingsService::class)->update([
        'mode' => MembershipSetting::MODE_PAID,
        'monthly_price' => 49,
        'grace_days' => 0,
    ]);

    $this->get(route('register'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('auth/register')
            ->where('membership.mode', 'paid')
            ->where('membership.monthly_price', 49)
            ->where('membership.is_paid', true)
        );
});
