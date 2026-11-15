<?php

use App\Enums\RoleEnum;
use App\Models\CoffeeMachine;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed([PermissionSeeder::class, RoleSeeder::class]);

    $this->admin = User::factory()->create();
    $this->admin->assignRole(RoleEnum::SUPER_ADMIN->value);
});

test('guests cannot access coffee machines', function () {
    $this->get(route('admin.coffee-machines.index'))->assertRedirect(route('login'));
});

test('users without permission are forbidden from coffee machines', function () {
    $plain = User::factory()->create();

    $this->actingAs($plain)
        ->get(route('admin.coffee-machines.index'))
        ->assertForbidden();
});

test('super admin can view the coffee machines list', function () {
    CoffeeMachine::factory()->create(['title' => 'Single-cup brewer']);

    $this->actingAs($this->admin)
        ->get(route('admin.coffee-machines.index'))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('admin/coffee-machines/index')
                ->has('coffeeMachines.data', 1)
                ->has('filters')
        );
});

test('a coffee machine can be created', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.coffee-machines.store'), [
            'title' => 'Batch brewer',
            'text' => 'Large pots for meetings.',
            'tag' => '50+ people',
            'sort_order' => 3,
            'is_active' => true,
        ])
        ->assertRedirect(route('admin.coffee-machines.index'));

    $machine = CoffeeMachine::query()->where('title', 'Batch brewer')->sole();

    expect($machine->tag)->toBe('50+ people')
        ->and($machine->sort_order)->toBe(3)
        ->and($machine->is_active)->toBeTrue();
});

test('a coffee machine can be created with an image', function () {
    Storage::fake('public');

    $this->actingAs($this->admin)
        ->post(route('admin.coffee-machines.store'), [
            'title' => 'Bean-to-cup machine',
            'image' => UploadedFile::fake()->image('machine.jpg'),
        ])
        ->assertRedirect(route('admin.coffee-machines.index'));

    $machine = CoffeeMachine::query()->where('title', 'Bean-to-cup machine')->sole();

    expect($machine->image)->not->toBeNull();
    Storage::disk('public')->assertExists($machine->image);
});

test('creating a coffee machine validates required fields', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.coffee-machines.store'), [
            'title' => '',
        ])
        ->assertSessionHasErrors(['title']);
});

test('a coffee machine can be updated', function () {
    $machine = CoffeeMachine::factory()->create(['title' => 'Old title']);

    $this->actingAs($this->admin)
        ->put(route('admin.coffee-machines.update', $machine), [
            'title' => 'Updated title',
            'sort_order' => 5,
            'is_active' => false,
        ])
        ->assertRedirect(route('admin.coffee-machines.index'));

    $machine->refresh();

    expect($machine->title)->toBe('Updated title')
        ->and($machine->sort_order)->toBe(5)
        ->and($machine->is_active)->toBeFalse();
});

test('a coffee machine can be deleted and its stored image is removed', function () {
    Storage::fake('public');
    Storage::disk('public')->put('coffee-machines/test.jpg', 'x');

    $machine = CoffeeMachine::factory()->create(['image' => 'coffee-machines/test.jpg']);

    $this->actingAs($this->admin)
        ->delete(route('admin.coffee-machines.destroy', $machine))
        ->assertRedirect();

    expect(CoffeeMachine::query()->whereKey($machine->id)->exists())->toBeFalse();
    Storage::disk('public')->assertMissing('coffee-machines/test.jpg');
});
