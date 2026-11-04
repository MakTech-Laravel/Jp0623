<?php

use App\Enums\RoleEnum;
use App\Models\Machine;
use App\Models\MachineCategory;
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

test('guests cannot access machine categories', function () {
    $this->get(route('admin.machine-categories.index'))->assertRedirect(route('login'));
});

test('users without permission are forbidden from machine categories', function () {
    $plain = User::factory()->create();

    $this->actingAs($plain)
        ->get(route('admin.machine-categories.index'))
        ->assertForbidden();
});

test('super admin can view the machine categories list', function () {
    MachineCategory::factory()->create(['name' => 'Combo']);

    $this->actingAs($this->admin)
        ->get(route('admin.machine-categories.index'))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('admin/machine-categories/index')
                ->has('categories.data', 1)
                ->has('filters')
        );
});

test('a machine category can be created', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.machine-categories.store'), [
            'name' => 'Combo',
            'description' => 'Snack and drink machines',
            'sort_order' => 1,
            'is_active' => true,
        ])
        ->assertRedirect(route('admin.machine-categories.index'));

    $category = MachineCategory::query()->where('name', 'Combo')->sole();

    expect($category->slug)->toBe('combo')
        ->and($category->is_active)->toBeTrue();
});

test('creating a machine category validates required fields', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.machine-categories.store'), [
            'name' => '',
        ])
        ->assertSessionHasErrors(['name']);
});

test('a machine category can be updated', function () {
    $category = MachineCategory::factory()->create(['name' => 'Snack']);

    $this->actingAs($this->admin)
        ->put(route('admin.machine-categories.update', $category), [
            'name' => 'Fresh food',
            'slug' => 'fresh-food',
            'description' => 'Updated',
            'sort_order' => 5,
            'is_active' => false,
        ])
        ->assertRedirect(route('admin.machine-categories.index'));

    $category->refresh();

    expect($category->name)->toBe('Fresh food')
        ->and($category->slug)->toBe('fresh-food')
        ->and($category->is_active)->toBeFalse();
});

test('a machine category without machines can be deleted', function () {
    $category = MachineCategory::factory()->create();

    $this->actingAs($this->admin)
        ->delete(route('admin.machine-categories.destroy', $category))
        ->assertRedirect();

    expect(MachineCategory::query()->whereKey($category->id)->exists())->toBeFalse();
});

test('a machine category with machines cannot be deleted', function () {
    $category = MachineCategory::factory()->create();
    Machine::factory()->create(['machine_category_id' => $category->id]);

    $this->actingAs($this->admin)
        ->from(route('admin.machine-categories.index'))
        ->delete(route('admin.machine-categories.destroy', $category))
        ->assertRedirect(route('admin.machine-categories.index'));

    expect(MachineCategory::query()->whereKey($category->id)->exists())->toBeTrue();
});
