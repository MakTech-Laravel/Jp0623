<?php

use App\Enums\RoleEnum;
use App\Models\CoffeeServiceRequest;
use App\Models\LeadPurchase;
use App\Models\OperatorProfile;
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

function makeOperator(array $profile = []): User
{
    $user = User::factory()->create();
    $user->assignRole(RoleEnum::USER->value);
    OperatorProfile::factory()->create(['user_id' => $user->id] + $profile);

    return $user;
}

test('operators see new and reviewed vending and coffee requests as leads', function () {
    VendingRequest::factory()->create(['status' => 'new']);
    VendingRequest::factory()->reviewed()->create();
    CoffeeServiceRequest::factory()->create(['status' => 'new']);
    CoffeeServiceRequest::factory()->reviewed()->create();
    CoffeeServiceRequest::factory()->create(['status' => 'closed']);

    $this->actingAs(makeOperator())
        ->get(route('leads.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('operator/leads/index')
            ->has('leads', 4)
            ->where('leads.0.price', (int) config('leads.price'))
        );
});

test('lead contact details are hidden until the lead is purchased', function () {
    $request = VendingRequest::factory()->create([
        'business_name' => 'Secret Corp',
        'address' => '99 Hidden Ave',
        'contact_name' => 'Hidden Person',
        'email' => 'hidden@example.com',
        'phone' => '555-0100',
    ]);
    $coffee = CoffeeServiceRequest::factory()->create([
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

    expect($request->exists)->toBeTrue()->and($coffee->exists)->toBeTrue();
});

test('buying a lead unlocks its details and removes it from the list', function () {
    $request = VendingRequest::factory()->create([
        'business_name' => 'Unlocked Corp',
        'contact_name' => 'Visible Person',
        'email' => 'visible@example.com',
        'phone' => '555-0199',
    ]);
    $operator = makeOperator();

    $this->actingAs($operator)
        ->post(route('leads.purchase', ['source' => 'vending', 'lead' => $request->id]))
        ->assertRedirect(route('leads.purchased'));

    $this->assertDatabaseHas('lead_purchases', [
        'user_id' => $operator->id,
        'leadable_type' => 'vending',
        'leadable_id' => $request->id,
        'price' => (int) config('leads.price'),
    ]);

    $this->actingAs($operator)
        ->get(route('leads.index'))
        ->assertInertia(fn (Assert $page) => $page->has('leads', 0));

    $this->actingAs($operator)
        ->get(route('leads.purchased'))
        ->assertInertia(fn (Assert $page) => $page
            ->has('purchasedLeads', 1)
            ->where('purchasedLeads.0.id', 'V-'.$request->id)
            ->where('purchasedLeads.0.name', 'Unlocked Corp')
            ->where('purchasedLeads.0.contact_name', 'Visible Person')
            ->where('purchasedLeads.0.contact_phone', '555-0199')
            ->where('purchasedLeads.0.email', 'visible@example.com')
        );
});

test('several operators can buy the same lead and buying twice is idempotent', function () {
    $coffee = CoffeeServiceRequest::factory()->create();
    $first = makeOperator();
    $second = makeOperator();

    $url = route('leads.purchase', ['source' => 'coffee', 'lead' => $coffee->id]);

    $this->actingAs($first)->post($url)->assertRedirect();
    $this->actingAs($first)->post($url)->assertRedirect();
    $this->actingAs($second)
        ->get(route('leads.index'))
        ->assertInertia(fn (Assert $page) => $page->has('leads', 1));
    $this->actingAs($second)->post($url)->assertRedirect();

    expect(LeadPurchase::query()->count())->toBe(2);
});

test('closed or unknown leads cannot be purchased', function () {
    $closed = CoffeeServiceRequest::factory()->create(['status' => 'closed']);
    $operator = makeOperator();

    $this->actingAs($operator)
        ->post(route('leads.purchase', ['source' => 'coffee', 'lead' => $closed->id]))
        ->assertNotFound();

    $this->actingAs($operator)
        ->post(route('leads.purchase', ['source' => 'vending', 'lead' => 9999]))
        ->assertNotFound();

    $this->actingAs($operator)
        ->post('/leads/other/1/purchase')
        ->assertNotFound();
});

test('guests cannot buy leads', function () {
    $request = VendingRequest::factory()->create();

    $this->post(route('leads.purchase', ['source' => 'vending', 'lead' => $request->id]))
        ->assertRedirect(route('login'));
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
