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
        );
});

test('admins see the admin dashboard overview', function () {
    $admin = User::factory()->create();
    $admin->assignRole(RoleEnum::ADMIN->value);

    $this->actingAs($admin)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('admin/dashboard')
                ->has('stats.operators')
                ->has('stats.admins')
                ->has('stats.machines')
                ->has('stats.categories')
                ->has('stats.products')
                ->has('stats.vendingRequests.total')
                ->has('stats.vendingRequests.new')
                ->has('stats.coffeeRequests.total')
                ->has('stats.coffeeRequests.new')
                ->has('recentVendingRequests')
                ->has('recentCoffeeRequests')
        );
});

test('super admins see the admin dashboard overview', function () {
    $admin = User::factory()->create();
    $admin->assignRole(RoleEnum::SUPER_ADMIN->value);

    $this->actingAs($admin)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('admin/dashboard')
                ->has('stats')
                ->has('recentVendingRequests')
                ->has('recentCoffeeRequests')
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

test('operators can open remaining portal pages', function (string $route, string $component, array $props = []) {
    $user = User::factory()->create();
    $user->assignRole(RoleEnum::USER->value);

    $this->actingAs($user)
        ->get(route($route))
        ->assertOk()
        ->assertInertia(function (Assert $page) use ($component, $props) {
            $page->component($component);

            foreach ($props as $prop) {
                $page->has($prop);
            }
        });
})->with([
    'tickets' => ['leads.tickets', 'operator/leads/tickets', ['tickets']],
    'submit' => ['leads.submit', 'operator/leads/submit', []],
    'commission' => ['leads.commission', 'operator/leads/commission', ['kpis', 'paypalEmail', 'submittedLeads', 'commissions', 'payouts']],
    'faq' => ['leads.faq', 'operator/leads/faq', ['faqs']],
    'routes' => ['routes.index', 'operator/routes/index', ['routes']],
    'sell route' => ['routes.sell', 'operator/routes/sell', []],
    'account' => ['account.index', 'operator/account/index', ['account']],
    'location' => ['account.location', 'operator/account/location', ['primaryCity', 'changesRemaining', 'changesTotal']],
    'billing' => ['billing.index', 'operator/billing/index', ['paymentMethod', 'invoices']],
]);

test('guests are redirected from remaining portal pages', function (string $route) {
    $this->get(route($route))->assertRedirect(route('login'));
})->with([
    'leads.tickets',
    'leads.submit',
    'leads.commission',
    'leads.faq',
    'routes.index',
    'routes.sell',
    'account.index',
    'account.location',
    'billing.index',
]);
