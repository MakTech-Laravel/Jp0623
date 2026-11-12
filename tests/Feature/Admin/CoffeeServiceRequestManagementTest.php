<?php

use App\Enums\RoleEnum;
use App\Models\CoffeeServiceRequest;
use App\Models\LeadPurchase;
use App\Models\User;
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

test('guests cannot access coffee service requests', function () {
    $this->get(route('admin.coffee-service-requests.index'))
        ->assertRedirect(route('login'));
});

test('users without permission are forbidden from coffee service requests', function () {
    $plain = User::factory()->create();

    $this->actingAs($plain)
        ->get(route('admin.coffee-service-requests.index'))
        ->assertForbidden();
});

test('super admin can view the coffee service requests list', function () {
    CoffeeServiceRequest::factory()->create([
        'business_name' => 'Harbor Offices',
    ]);

    $this->actingAs($this->admin)
        ->get(route('admin.coffee-service-requests.index'))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('admin/coffee-service-requests/index')
                ->has('requests.data', 1)
                ->where('requests.data.0.is_sold', false)
                ->where('requests.data.0.is_held', false)
                ->has('filters.sale')
        );
});

test('a coffee service request can be viewed', function () {
    $request = CoffeeServiceRequest::factory()->create();

    $this->actingAs($this->admin)
        ->get(route('admin.coffee-service-requests.show', $request))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('admin/coffee-service-requests/show')
                ->where('coffeeServiceRequest.id', $request->id)
                ->where('coffeeServiceRequest.is_sold', false)
                ->where('coffeeServiceRequest.is_held', false)
                ->where('leadBuyer', null)
        );
});

test('sold and pending coffee leads expose sale flags and buyer', function () {
    $operator = makeOperator();
    $soldLead = CoffeeServiceRequest::factory()->create(['business_name' => 'Sold Coffee']);
    $pendingLead = CoffeeServiceRequest::factory()->create(['business_name' => 'Pending Coffee']);
    $availableLead = CoffeeServiceRequest::factory()->create(['business_name' => 'Open Coffee']);

    LeadPurchase::factory()->create([
        'user_id' => $operator->id,
        'leadable_type' => 'coffee',
        'leadable_id' => $soldLead->id,
    ]);

    LeadPurchase::factory()->pending()->create([
        'user_id' => $operator->id,
        'leadable_type' => 'coffee',
        'leadable_id' => $pendingLead->id,
    ]);

    $this->actingAs($this->admin)
        ->get(route('admin.coffee-service-requests.index', ['sale' => 'sold']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('requests.data', 1)
            ->where('requests.data.0.id', $soldLead->id)
            ->where('requests.data.0.is_sold', true)
            ->where('filters.sale', 'sold')
        );

    $this->actingAs($this->admin)
        ->get(route('admin.coffee-service-requests.index', ['sale' => 'processing']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('requests.data', 1)
            ->where('requests.data.0.id', $pendingLead->id)
            ->where('requests.data.0.is_held', true)
            ->where('requests.data.0.is_sold', false)
        );

    $this->actingAs($this->admin)
        ->get(route('admin.coffee-service-requests.index', ['sale' => 'available']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('requests.data', 1)
            ->where('requests.data.0.id', $availableLead->id)
        );

    $this->actingAs($this->admin)
        ->get(route('admin.coffee-service-requests.show', $soldLead))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('coffeeServiceRequest.is_sold', true)
            ->where('leadBuyer.email', $operator->email)
        );
});

test('a coffee service request status can be updated', function () {
    $request = CoffeeServiceRequest::factory()->create([
        'status' => CoffeeServiceRequest::STATUS_NEW,
    ]);

    $this->actingAs($this->admin)
        ->put(route('admin.coffee-service-requests.update', $request), [
            'status' => CoffeeServiceRequest::STATUS_REVIEWED,
        ])
        ->assertRedirect();

    expect($request->fresh()->status)->toBe(CoffeeServiceRequest::STATUS_REVIEWED);

    $this->actingAs($this->admin)
        ->put(route('admin.coffee-service-requests.update', $request), [
            'status' => CoffeeServiceRequest::STATUS_CLOSED,
        ])
        ->assertRedirect();

    expect($request->fresh()->status)->toBe(CoffeeServiceRequest::STATUS_CLOSED);
});

test('a coffee service request can be deleted', function () {
    $request = CoffeeServiceRequest::factory()->create();

    $this->actingAs($this->admin)
        ->delete(route('admin.coffee-service-requests.destroy', $request))
        ->assertRedirect(route('admin.coffee-service-requests.index'));

    expect(CoffeeServiceRequest::query()->whereKey($request->id)->exists())->toBeFalse();
});
