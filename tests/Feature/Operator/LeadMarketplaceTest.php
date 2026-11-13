<?php

use App\Models\CoffeeServiceRequest;
use App\Models\LeadPurchase;
use App\Models\User;
use App\Models\VendingRequest;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed([PermissionSeeder::class, RoleSeeder::class]);
});

test('operators see new and reviewed vending and coffee requests as leads', function () {
    VendingRequest::factory()->create(['status' => 'new']);
    VendingRequest::factory()->reviewed()->create();
    CoffeeServiceRequest::factory()->create(['status' => 'new']);
    CoffeeServiceRequest::factory()->reviewed()->create();
    CoffeeServiceRequest::factory()->create(['status' => 'closed']);
    VendingRequest::factory()->unpriced()->create(['status' => 'new']);

    $this->actingAs(makeOperator())
        ->get(route('leads.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('operator/leads/index')
            ->has('leads', 4)
            ->where('leads.0.price', (int) config('leads.price'))
        );
});

test('unpriced leads stay hidden until an admin sets a price', function () {
    $lead = VendingRequest::factory()->unpriced()->create(['status' => 'new']);

    $operator = makeOperator();

    $this->actingAs($operator)
        ->get(route('leads.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->has('leads', 0));

    $lead->update(['price' => 175]);

    $this->actingAs($operator)
        ->get(route('leads.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('leads', 1)
            ->where('leads.0.price', 175)
        );
});

test('lead contact details are hidden until the lead is purchased', function () {
    VendingRequest::factory()->create([
        'business_name' => 'Secret Corp',
        'address' => '99 Hidden Ave',
        'contact_name' => 'Hidden Person',
        'email' => 'hidden@example.com',
        'phone' => '555-0100',
    ]);
    CoffeeServiceRequest::factory()->create([
        'business_name' => 'Secret Coffee Co',
        'contact_name' => 'Hidden Barista',
    ]);

    $operator = makeOperator();

    foreach (['leads.index', 'dashboard'] as $routeName) {
        $response = $this->actingAs($operator)->get(route($routeName));

        $response->assertOk();

        $body = json_encode($response->viewData('page')['props']);

        foreach (['Secret Corp', '99 Hidden Ave', 'Hidden Person', 'hidden@example.com', '555-0100', 'Secret Coffee Co', 'Hidden Barista'] as $secret) {
            expect($body)->not->toContain($secret);
        }
    }
});

test('leads held by an operator are hidden from every other operator', function () {
    $lead = VendingRequest::factory()->create();
    VendingRequest::factory()->create();

    $buyer = makeOperator();
    $other = makeOperator();

    LeadPurchase::factory()->create([
        'user_id' => $buyer->id,
        'leadable_id' => $lead->id,
    ]);

    foreach ([$buyer, $other] as $operator) {
        $this->actingAs($operator)
            ->get(route('leads.index'))
            ->assertInertia(fn (Assert $page) => $page->has('leads', 1));
    }

    $this->actingAs($other)
        ->get(route('dashboard'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('kpis.0.value', '1')
            ->where('kpis.1.value', '0')
        );
});

test('failed purchases release the lead back to the list', function () {
    $lead = VendingRequest::factory()->create();

    LeadPurchase::factory()->failed()->create([
        'user_id' => makeOperator()->id,
        'leadable_id' => $lead->id,
    ]);

    $this->actingAs(makeOperator())
        ->get(route('leads.index'))
        ->assertInertia(fn (Assert $page) => $page->has('leads', 1));
});

test('distance is calculated only when both sides have coordinates', function () {
    VendingRequest::factory()->create(['latitude' => 29.7604, 'longitude' => -95.3698]);
    VendingRequest::factory()->create(['latitude' => null, 'longitude' => null]);

    $operator = makeOperator(['latitude' => 29.7604, 'longitude' => -95.3698]);

    $this->actingAs($operator)
        ->get(route('leads.index'))
        ->assertInertia(function (Assert $page) {
            $page->has('leads', 2);
            $distances = collect($page->toArray()['props']['leads'])->pluck('dist')->all();

            expect($distances)->toContain(0)->toContain(null);
        });
});

test('seeded demo operator has a saved location and sees lead distances', function () {
    $this->seed(UserSeeder::class);

    $operator = User::query()->where('email', 'user@dev.com')->firstOrFail();

    expect($operator->operatorProfile)->not->toBeNull()
        ->and($operator->operatorProfile->latitude)->not->toBeNull()
        ->and($operator->operatorProfile->longitude)->not->toBeNull();

    VendingRequest::factory()->create([
        'latitude' => 47.3684498,
        'longitude' => -94.8623759,
    ]);

    $this->actingAs($operator)
        ->get(route('leads.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->has('leads', 1)
            ->where('leads.0.dist', fn ($dist): bool => is_int($dist) && $dist > 100 && $dist < 400)
        );

    $this->actingAs($operator)
        ->get(route('dashboard'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('newestLeads.0.dist', fn ($dist): bool => is_int($dist))
        );
});

test('dashboard counts are based on real leads', function () {
    VendingRequest::factory()->count(2)->create();
    $operator = makeOperator();
    LeadPurchase::factory()->create([
        'user_id' => $operator->id,
        'leadable_id' => VendingRequest::factory(),
    ]);

    $this->actingAs($operator)
        ->get(route('dashboard'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('kpis.0.value', '2')
            ->where('kpis.1.value', '1')
            ->has('newestLeads', 2)
        );
});
