<?php

use App\Enums\RoleEnum;
use App\Models\CoffeeType;
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

test('guests cannot access coffee types', function () {
    $this->get(route('admin.coffee-types.index'))->assertRedirect(route('login'));
});

test('users without permission are forbidden from coffee types', function () {
    $plain = User::factory()->create();

    $this->actingAs($plain)
        ->get(route('admin.coffee-types.index'))
        ->assertForbidden();
});

test('super admin can view the coffee types list', function () {
    CoffeeType::factory()->create(['label' => 'Decaf']);

    $this->actingAs($this->admin)
        ->get(route('admin.coffee-types.index'))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('admin/coffee-types/index')
                ->has('coffeeTypes.data', 1)
                ->has('filters')
        );
});

test('a coffee type can be created', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.coffee-types.store'), [
            'label' => 'Cold brew',
            'sort_order' => 4,
            'is_active' => true,
        ])
        ->assertRedirect(route('admin.coffee-types.index'));

    $type = CoffeeType::query()->where('label', 'Cold brew')->sole();

    expect($type->is_active)->toBeTrue()
        ->and($type->sort_order)->toBe(4);
});

test('creating a coffee type validates required fields', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.coffee-types.store'), ['label' => ''])
        ->assertSessionHasErrors(['label']);
});

test('creating a coffee type rejects a duplicate label', function () {
    CoffeeType::factory()->create(['label' => 'Decaf']);

    $this->actingAs($this->admin)
        ->post(route('admin.coffee-types.store'), ['label' => 'Decaf'])
        ->assertSessionHasErrors(['label']);
});

test('a coffee type can be updated', function () {
    $type = CoffeeType::factory()->create(['label' => 'Old label']);

    $this->actingAs($this->admin)
        ->put(route('admin.coffee-types.update', $type), [
            'label' => 'Updated label',
            'sort_order' => 5,
            'is_active' => false,
        ])
        ->assertRedirect(route('admin.coffee-types.index'));

    $type->refresh();

    expect($type->label)->toBe('Updated label')
        ->and($type->sort_order)->toBe(5)
        ->and($type->is_active)->toBeFalse();
});

test('a coffee type can be deleted', function () {
    $type = CoffeeType::factory()->create();

    $this->actingAs($this->admin)
        ->delete(route('admin.coffee-types.destroy', $type))
        ->assertRedirect();

    expect(CoffeeType::query()->whereKey($type->id)->exists())->toBeFalse();
});
