<?php

use App\Enums\RoleEnum;
use App\Models\CoffeeServiceRequest;
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
                ->has('filters')
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
