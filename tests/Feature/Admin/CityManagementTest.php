<?php

use App\Enums\RoleEnum;
use App\Models\City;
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

test('guests cannot access cities', function () {
    $this->get(route('admin.cities.index'))->assertRedirect(route('login'));
});

test('users without permission are forbidden from cities', function () {
    $plain = User::factory()->create();

    $this->actingAs($plain)
        ->get(route('admin.cities.index'))
        ->assertForbidden();
});

test('super admin can view the cities list', function () {
    City::factory()->create(['city' => 'Houston', 'state' => 'TX']);

    $this->actingAs($this->admin)
        ->get(route('admin.cities.index'))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('admin/cities/index')
                ->has('cities.data', 1)
                ->has('filters')
        );
});

test('a city can be created and the state is upper-cased', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.cities.store'), [
            'city' => 'Austin',
            'state' => 'tx',
            'sort_order' => 4,
            'is_active' => true,
        ])
        ->assertRedirect(route('admin.cities.index'));

    $city = City::query()->where('city', 'Austin')->sole();

    expect($city->state)->toBe('TX')
        ->and($city->sort_order)->toBe(4)
        ->and($city->is_active)->toBeTrue();
});

test('a city can be saved with location details from a suggestion', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.cities.store'), [
            'city' => 'Houston',
            'state' => 'TX',
            'postal_code' => '77002',
            'latitude' => 29.7589382,
            'longitude' => -95.3676974,
        ])
        ->assertRedirect(route('admin.cities.index'));

    $city = City::query()->where('city', 'Houston')->sole();

    expect($city->postal_code)->toBe('77002')
        ->and($city->latitude)->toBe(29.7589382)
        ->and($city->longitude)->toBe(-95.3676974);
});

test('a city rejects out of range coordinates', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.cities.store'), [
            'city' => 'Nowhere',
            'state' => 'TX',
            'latitude' => 123,
            'longitude' => -300,
        ])
        ->assertSessionHasErrors(['latitude', 'longitude']);
});

test('creating a city validates required fields and state format', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.cities.store'), ['city' => '', 'state' => 'Texas'])
        ->assertSessionHasErrors(['city', 'state']);
});

test('the same city and state cannot be created twice but another state can', function () {
    City::factory()->create(['city' => 'Columbus', 'state' => 'OH']);

    $this->actingAs($this->admin)
        ->post(route('admin.cities.store'), ['city' => 'Columbus', 'state' => 'OH'])
        ->assertSessionHasErrors(['city']);

    $this->actingAs($this->admin)
        ->post(route('admin.cities.store'), ['city' => 'Columbus', 'state' => 'GA'])
        ->assertRedirect(route('admin.cities.index'));

    expect(City::query()->where('city', 'Columbus')->count())->toBe(2);
});

test('a city can be updated', function () {
    $city = City::factory()->create(['city' => 'Old', 'state' => 'TX']);

    $this->actingAs($this->admin)
        ->put(route('admin.cities.update', $city), [
            'city' => 'New',
            'state' => 'CA',
            'sort_order' => 7,
            'is_active' => false,
        ])
        ->assertRedirect(route('admin.cities.index'));

    $city->refresh();

    expect($city->city)->toBe('New')
        ->and($city->state)->toBe('CA')
        ->and($city->sort_order)->toBe(7)
        ->and($city->is_active)->toBeFalse();
});

test('a city can be updated without changing its own name', function () {
    $city = City::factory()->create(['city' => 'Same', 'state' => 'TX']);

    $this->actingAs($this->admin)
        ->put(route('admin.cities.update', $city), [
            'city' => 'Same',
            'state' => 'TX',
            'sort_order' => 9,
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('admin.cities.index'));

    expect($city->refresh()->sort_order)->toBe(9);
});

test('a city can be deleted', function () {
    $city = City::factory()->create();

    $this->actingAs($this->admin)
        ->delete(route('admin.cities.destroy', $city))
        ->assertRedirect();

    expect(City::query()->whereKey($city->id)->exists())->toBeFalse();
});
