<?php

use App\Enums\RoleEnum;
use App\Models\LocationType;
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

test('guests cannot access location types', function () {
    $this->get(route('admin.location-types.index'))->assertRedirect(route('login'));
});

test('users without permission are forbidden from location types', function () {
    $plain = User::factory()->create();

    $this->actingAs($plain)
        ->get(route('admin.location-types.index'))
        ->assertForbidden();
});

test('super admin can view the location types list', function () {
    LocationType::factory()->create(['name' => 'Business / office']);

    $this->actingAs($this->admin)
        ->get(route('admin.location-types.index'))
        ->assertOk()
        ->assertInertia(
            fn(Assert $page) => $page
                ->component('admin/location-types/index')
                ->has('locationTypes.data', 1)
                ->has('filters')
        );
});

test('a location type can be created', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.location-types.store'), [
            'name' => 'Retail store',
            'sort_order' => 1,
            'is_active' => true,
        ])
        ->assertRedirect(route('admin.location-types.index'));

    $locationType = LocationType::query()->where('name', 'Retail store')->sole();

    expect($locationType->is_active)->toBeTrue()
        ->and($locationType->sort_order)->toBe(1);
});

test('creating a location type validates required fields', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.location-types.store'), [
            'name' => '',
        ])
        ->assertSessionHasErrors(['name']);
});

test('a location type can be updated', function () {
    $locationType = LocationType::factory()->create(['name' => 'Old name']);

    $this->actingAs($this->admin)
        ->put(route('admin.location-types.update', $locationType), [
            'name' => 'Updated name',
            'sort_order' => 5,
            'is_active' => false,
        ])
        ->assertRedirect(route('admin.location-types.index'));

    $locationType->refresh();

    expect($locationType->name)->toBe('Updated name')
        ->and($locationType->sort_order)->toBe(5)
        ->and($locationType->is_active)->toBeFalse();
});

test('a location type can be deleted', function () {
    $locationType = LocationType::factory()->create();

    $this->actingAs($this->admin)
        ->delete(route('admin.location-types.destroy', $locationType))
        ->assertRedirect();

    expect(LocationType::query()->whereKey($locationType->id)->exists())->toBeFalse();
});
