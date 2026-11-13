<?php

use App\Enums\RoleEnum;
use App\Models\MembershipSetting;
use App\Models\OperatorMembership;
use App\Models\User;
use App\Services\MembershipSettingsService;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed([PermissionSeeder::class, RoleSeeder::class]);

    $this->admin = User::factory()->create();
    $this->admin->assignRole(RoleEnum::SUPER_ADMIN->value);
});

function setMembershipMode(string $mode, int $monthlyPrice = 29): MembershipSetting
{
    return app(MembershipSettingsService::class)->update([
        'mode' => $mode,
        'monthly_price' => $monthlyPrice,
        'grace_days' => 0,
    ]);
}

test('guests cannot access membership settings', function () {
    $this->get(route('admin.membership-settings.edit'))
        ->assertRedirect(route('login'));
});

test('users without permission are forbidden from membership settings', function () {
    $plain = User::factory()->create();

    $this->actingAs($plain)
        ->get(route('admin.membership-settings.edit'))
        ->assertForbidden();
});

test('super admin can view and update membership settings', function () {
    setMembershipMode(MembershipSetting::MODE_FREE, 29);

    $this->actingAs($this->admin)
        ->get(route('admin.membership-settings.edit'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/membership-settings/edit')
            ->where('settings.mode', 'free')
            ->where('settings.monthly_price', 29)
        );

    $this->actingAs($this->admin)
        ->put(route('admin.membership-settings.update'), [
            'mode' => MembershipSetting::MODE_PAID,
            'monthly_price' => 39,
            'grace_days' => 0,
        ])
        ->assertRedirect();

    $settings = app(MembershipSettingsService::class)->current();

    expect($settings->mode)->toBe(MembershipSetting::MODE_PAID)
        ->and($settings->monthly_price)->toBe(39);
});

test('mode flips do not rewrite operator membership statuses or billing dates', function () {
    $freeOperator = makeOperator();
    $paidOperator = makeOperator();

    OperatorMembership::factory()->create([
        'user_id' => $freeOperator->id,
        'status' => OperatorMembership::STATUS_ACTIVE_FREE,
    ]);

    $paidMembership = OperatorMembership::factory()->active()->create([
        'user_id' => $paidOperator->id,
    ]);
    $nextBillingAt = $paidMembership->next_billing_at?->toIso8601String();

    setMembershipMode(MembershipSetting::MODE_PAID, 29);
    setMembershipMode(MembershipSetting::MODE_FREE, 29);
    setMembershipMode(MembershipSetting::MODE_PAID, 29);

    expect($freeOperator->membership()->first()->status)
        ->toBe(OperatorMembership::STATUS_ACTIVE_FREE)
        ->and($paidOperator->membership()->first()->status)
        ->toBe(OperatorMembership::STATUS_ACTIVE)
        ->and($paidOperator->membership()->first()->next_billing_at?->toIso8601String())
        ->toBe($nextBillingAt);
});

test('paid then free then paid preserves remaining paid period access', function () {
    setMembershipMode(MembershipSetting::MODE_PAID, 29);

    $operator = makeOperator();
    OperatorMembership::factory()->active()->create([
        'user_id' => $operator->id,
        'current_period_end' => now()->addDays(20),
        'next_billing_at' => now()->addDays(20),
    ]);

    setMembershipMode(MembershipSetting::MODE_FREE, 29);
    setMembershipMode(MembershipSetting::MODE_PAID, 29);

    $this->actingAs($operator)
        ->get(route('leads.index'))
        ->assertOk();

    $this->actingAs($operator)
        ->get(route('subscription.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('membership.status', OperatorMembership::STATUS_ACTIVE)
            ->where('membership.needs_payment', false)
            ->where('membership.allows_access', true)
        );
});

test('never-paid operators remain gated when mode is paid', function () {
    $operator = makeOperator();
    OperatorMembership::factory()->create([
        'user_id' => $operator->id,
        'status' => OperatorMembership::STATUS_ACTIVE_FREE,
    ]);

    setMembershipMode(MembershipSetting::MODE_PAID, 29);

    $this->actingAs($operator)
        ->get(route('leads.index'))
        ->assertRedirect(route('subscription.index'));
});

test('corrupt membership settings cache recovers to a MembershipSetting', function () {
    $settings = setMembershipMode(MembershipSetting::MODE_PAID, 41);

    // Simulate a legacy/serialized-model cache entry that cannot be typed as MembershipSetting.
    Cache::forever('membership.settings', 'not-an-attributes-array');

    $resolved = app(MembershipSettingsService::class)->current();

    expect($resolved)->toBeInstanceOf(MembershipSetting::class)
        ->and($resolved->id)->toBe($settings->id)
        ->and($resolved->mode)->toBe(MembershipSetting::MODE_PAID)
        ->and($resolved->monthly_price)->toBe(41)
        ->and(Cache::get('membership.settings'))->toBeArray()
        ->and(Cache::get('membership.settings')['mode'])->toBe(MembershipSetting::MODE_PAID);
});
