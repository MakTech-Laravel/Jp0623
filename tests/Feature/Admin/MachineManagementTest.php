<?php

use App\Enums\RoleEnum;
use App\Models\Machine;
use App\Models\MachineCategory;
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

    $this->category = MachineCategory::factory()->create([
        'name' => 'Combo',
        'slug' => 'combo',
    ]);
});

test('guests cannot access machines', function () {
    $this->get(route('admin.machines.index'))->assertRedirect(route('login'));
});

test('users without permission are forbidden from machines', function () {
    $plain = User::factory()->create();

    $this->actingAs($plain)
        ->get(route('admin.machines.index'))
        ->assertForbidden();
});

test('super admin can view the machines list', function () {
    Machine::factory()->create([
        'machine_category_id' => $this->category->id,
        'name' => 'Snack & Drink Combo',
    ]);

    $this->actingAs($this->admin)
        ->get(route('admin.machines.index'))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('admin/machines/index')
                ->has('machines.data', 1)
                ->has('categories')
                ->has('filters')
        );
});

test('a machine can be created with a category', function () {
    Storage::fake('public');

    $this->actingAs($this->admin)
        ->post(route('admin.machines.store'), [
            'machine_category_id' => $this->category->id,
            'name' => 'Snack & Drink Combo',
            'kind' => 'combo',
            'tags' => 'Combo, Card + phone',
            'description' => 'One machine for snacks and cold drinks.',
            'capacity' => '35 snack + 40 drink selections',
            'dimensions' => '72" H × 39" W × 35" D',
            'best_for' => 'Offices and break rooms',
            'status' => 'Available nationwide',
            'is_featured' => true,
            'is_active' => true,
            'sort_order' => 1,
            'image' => UploadedFile::fake()->image('combo.jpg'),
        ])
        ->assertRedirect(route('admin.machines.index'));

    $machine = Machine::query()->where('name', 'Snack & Drink Combo')->sole();

    expect($machine->slug)->toBe('snack-drink-combo')
        ->and($machine->machine_category_id)->toBe($this->category->id)
        ->and($machine->tags)->toBe(['Combo', 'Card + phone'])
        ->and($machine->is_featured)->toBeTrue()
        ->and($machine->image)->not->toBeNull();

    Storage::disk('public')->assertExists($machine->image);
});

test('creating a machine requires a category and name', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.machines.store'), [
            'machine_category_id' => null,
            'name' => '',
            'kind' => '',
        ])
        ->assertSessionHasErrors(['machine_category_id', 'name', 'kind']);
});

test('a machine can be viewed', function () {
    $machine = Machine::factory()->create([
        'machine_category_id' => $this->category->id,
    ]);

    $this->actingAs($this->admin)
        ->get(route('admin.machines.show', $machine))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('admin/machines/show')
                ->where('machine.id', $machine->id)
                ->has('machine.category')
        );
});

test('a machine can be updated', function () {
    $machine = Machine::factory()->create([
        'machine_category_id' => $this->category->id,
        'name' => 'Old Name',
        'kind' => 'combo',
    ]);

    $otherCategory = MachineCategory::factory()->create([
        'name' => 'Snack',
        'slug' => 'snack',
    ]);

    $this->actingAs($this->admin)
        ->put(route('admin.machines.update', $machine), [
            'machine_category_id' => $otherCategory->id,
            'name' => 'Glass-Front Snack',
            'slug' => 'snack',
            'kind' => 'snack',
            'tags' => 'Snack, High capacity',
            'description' => 'Updated description',
            'status' => 'Available nationwide',
            'is_featured' => false,
            'is_active' => true,
            'sort_order' => 2,
        ])
        ->assertRedirect(route('admin.machines.index'));

    $machine->refresh();

    expect($machine->name)->toBe('Glass-Front Snack')
        ->and($machine->slug)->toBe('snack')
        ->and($machine->machine_category_id)->toBe($otherCategory->id)
        ->and($machine->tags)->toBe(['Snack', 'High capacity']);
});

test('a machine can be deleted', function () {
    Storage::fake('public');

    $path = UploadedFile::fake()->image('delete-me.jpg')->store('machines', 'public');

    $machine = Machine::factory()->create([
        'machine_category_id' => $this->category->id,
        'image' => $path,
    ]);

    $this->actingAs($this->admin)
        ->delete(route('admin.machines.destroy', $machine))
        ->assertRedirect();

    expect(Machine::query()->whereKey($machine->id)->exists())->toBeFalse();
    Storage::disk('public')->assertMissing($path);
});

test('updating a machine without a new image keeps the existing image', function () {
    $machine = Machine::factory()->create([
        'machine_category_id' => $this->category->id,
        'name' => 'Keep Image Machine',
        'kind' => 'combo',
        'image' => '/images/machines/Alpine-ST5000.png',
    ]);

    $this->actingAs($this->admin)
        ->put(route('admin.machines.update', $machine), [
            'machine_category_id' => $this->category->id,
            'name' => 'Keep Image Machine',
            'slug' => $machine->slug,
            'kind' => 'combo',
            'tags' => 'Combo',
            'description' => 'Updated without image',
            'status' => 'Available nationwide',
            'is_featured' => false,
            'is_active' => true,
            'sort_order' => 1,
            'image' => '',
        ])
        ->assertRedirect(route('admin.machines.index'));

    expect($machine->fresh()->image)->toBe('/images/machines/Alpine-ST5000.png');
});

test('updating a machine with a new image stores the upload', function () {
    Storage::fake('public');

    $machine = Machine::factory()->create([
        'machine_category_id' => $this->category->id,
        'name' => 'Replace Image Machine',
        'kind' => 'combo',
        'image' => '/images/machines/Alpine-ST5000.png',
    ]);

    $this->actingAs($this->admin)
        ->put(route('admin.machines.update', $machine), [
            'machine_category_id' => $this->category->id,
            'name' => 'Replace Image Machine',
            'slug' => $machine->slug,
            'kind' => 'combo',
            'tags' => 'Combo',
            'description' => 'Updated with image',
            'status' => 'Available nationwide',
            'is_featured' => false,
            'is_active' => true,
            'sort_order' => 1,
            'image' => UploadedFile::fake()->image('replacement.jpg'),
        ])
        ->assertRedirect(route('admin.machines.index'));

    $machine->refresh();

    expect($machine->image)->not->toBe('/images/machines/Alpine-ST5000.png')
        ->and($machine->image)->toStartWith('machines/')
        ->and($machine->image_url)->toBe('/storage/'.$machine->image);

    Storage::disk('public')->assertExists($machine->image);
});

test('an empty image string on update does not fail validation', function () {
    $machine = Machine::factory()->create([
        'machine_category_id' => $this->category->id,
        'kind' => 'combo',
    ]);

    $this->actingAs($this->admin)
        ->put(route('admin.machines.update', $machine), [
            'machine_category_id' => $this->category->id,
            'name' => $machine->name,
            'slug' => $machine->slug,
            'kind' => 'combo',
            'status' => 'Available nationwide',
            'is_featured' => false,
            'is_active' => true,
            'sort_order' => 0,
            'image' => '',
        ])
        ->assertSessionDoesntHaveErrors('image')
        ->assertRedirect(route('admin.machines.index'));
});

test('create machine form receives the next sort order', function () {
    Machine::factory()->create([
        'machine_category_id' => $this->category->id,
        'sort_order' => 5,
    ]);

    $this->actingAs($this->admin)
        ->get(route('admin.machines.create'))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('admin/machines/create')
                ->where('defaultSortOrder', 6)
        );
});

test('a machine created without sort order appends after existing rows', function () {
    Machine::factory()->create([
        'machine_category_id' => $this->category->id,
        'name' => 'Older Machine',
        'sort_order' => 3,
    ]);

    $this->actingAs($this->admin)
        ->post(route('admin.machines.store'), [
            'machine_category_id' => $this->category->id,
            'name' => 'Newest Machine',
            'kind' => 'combo',
            'status' => 'Available nationwide',
            'is_featured' => false,
            'is_active' => true,
        ])
        ->assertRedirect(route('admin.machines.index'));

    $newest = Machine::query()->where('name', 'Newest Machine')->sole();

    expect($newest->sort_order)->toBe(4);

    $this->actingAs($this->admin)
        ->get(route('admin.machines.index'))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->where('machines.data.0.name', 'Older Machine')
                ->where('machines.data.1.name', 'Newest Machine')
        );
});
