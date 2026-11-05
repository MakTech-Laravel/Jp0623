<?php

use App\Enums\RoleEnum;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed([PermissionSeeder::class, RoleSeeder::class]);
});

test('guests are redirected to the login page', function () {
    $this->get(route('dashboard'))->assertRedirect(route('login'));
});

test('operators see the operator dashboard overview', function () {
    $user = User::factory()->create();
    $user->assignRole(RoleEnum::USER->value);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('operator/dashboard')
                ->has('firstName')
                ->has('kpis')
                ->has('newestLeads')
                ->has('activity')
                ->has('trialBanner')
        );
});

test('admins see the admin dashboard placeholders', function () {
    $admin = User::factory()->create();
    $admin->assignRole(RoleEnum::ADMIN->value);

    $this->actingAs($admin)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page->component('admin/dashboard')
        );
});

test('super admins see the admin dashboard placeholders', function () {
    $admin = User::factory()->create();
    $admin->assignRole(RoleEnum::SUPER_ADMIN->value);

    $this->actingAs($admin)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page->component('admin/dashboard')
        );
});

test('operators can open the leads stub page', function () {
    $user = User::factory()->create();
    $user->assignRole(RoleEnum::USER->value);

    $this->actingAs($user)
        ->get(route('leads.index'))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('operator/leads/index')
                ->has('leads')
        );
});

test('operators can open the subscription stub page', function () {
    $user = User::factory()->create();
    $user->assignRole(RoleEnum::USER->value);

    $this->actingAs($user)
        ->get(route('subscription.index'))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('operator/subscription/index')
                ->has('subscription')
        );
});

test('operators can open the purchased location leads page', function () {
    $user = User::factory()->create();
    $user->assignRole(RoleEnum::USER->value);

    $this->actingAs($user)
        ->get(route('leads.purchased'))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('operator/leads/purchased')
                ->has('purchasedLeads')
                ->has('primaryCity')
        );
});

test('guests are redirected from purchased location leads', function () {
    $this->get(route('leads.purchased'))->assertRedirect(route('login'));
});
