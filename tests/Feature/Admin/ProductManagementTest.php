<?php

use App\Enums\RoleEnum;
use App\Models\Product;
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

test('guests cannot access products', function () {
    $this->get(route('admin.products.index'))->assertRedirect(route('login'));
});

test('users without permission are forbidden from products', function () {
    $plain = User::factory()->create();

    $this->actingAs($plain)
        ->get(route('admin.products.index'))
        ->assertForbidden();
});

test('super admin can view the products list', function () {
    Product::factory()->create([
        'name' => 'Beverages',
        'kind' => 'drink',
    ]);

    $this->actingAs($this->admin)
        ->get(route('admin.products.index'))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('admin/products/index')
                ->has('products.data', 1)
                ->has('filters')
        );
});

test('a product can be created', function () {
    Storage::fake('public');

    $this->actingAs($this->admin)
        ->post(route('admin.products.store'), [
            'name' => 'Beverages',
            'kind' => 'drink',
            'tags' => 'Bottled water, Cola & diet cola',
            'description' => 'Water, soda, sparkling water, juice and iced tea',
            'is_featured' => true,
            'is_active' => true,
            'sort_order' => 1,
            'image' => UploadedFile::fake()->image('drinks.jpg'),
        ])
        ->assertRedirect(route('admin.products.index'));

    $product = Product::query()->where('name', 'Beverages')->sole();

    expect($product->slug)->toBe('beverages')
        ->and($product->kind)->toBe('drink')
        ->and($product->tags)->toBe(['Bottled water', 'Cola & diet cola'])
        ->and($product->is_featured)->toBeTrue()
        ->and($product->image)->not->toBeNull();

    Storage::disk('public')->assertExists($product->image);
});

test('creating a product requires a name and kind', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.products.store'), [
            'name' => '',
            'kind' => '',
        ])
        ->assertSessionHasErrors(['name', 'kind']);
});

test('a product can be viewed', function () {
    $product = Product::factory()->create();

    $this->actingAs($this->admin)
        ->get(route('admin.products.show', $product))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('admin/products/show')
                ->where('product.id', $product->id)
        );
});

test('a product can be updated', function () {
    $product = Product::factory()->create([
        'name' => 'Old Name',
        'kind' => 'drink',
    ]);

    $this->actingAs($this->admin)
        ->put(route('admin.products.update', $product), [
            'name' => 'Healthy choices',
            'slug' => 'healthy',
            'kind' => 'healthy',
            'tags' => 'Baked chips, Veggie straws',
            'description' => 'Updated description',
            'is_featured' => false,
            'is_active' => true,
            'sort_order' => 2,
        ])
        ->assertRedirect(route('admin.products.index'));

    $product->refresh();

    expect($product->name)->toBe('Healthy choices')
        ->and($product->slug)->toBe('healthy')
        ->and($product->kind)->toBe('healthy')
        ->and($product->tags)->toBe(['Baked chips', 'Veggie straws']);
});

test('a product can be deleted', function () {
    Storage::fake('public');

    $path = UploadedFile::fake()->image('delete-me.jpg')->store('products', 'public');

    $product = Product::factory()->create([
        'image' => $path,
    ]);

    $this->actingAs($this->admin)
        ->delete(route('admin.products.destroy', $product))
        ->assertRedirect();

    expect(Product::query()->whereKey($product->id)->exists())->toBeFalse();
    Storage::disk('public')->assertMissing($path);
});

test('updating a product without a new image keeps the existing image', function () {
    $product = Product::factory()->create([
        'name' => 'Keep Image Product',
        'kind' => 'drink',
        'image' => '/images/products/drinks.jpg',
    ]);

    $this->actingAs($this->admin)
        ->put(route('admin.products.update', $product), [
            'name' => 'Keep Image Product',
            'slug' => $product->slug,
            'kind' => 'drink',
            'tags' => 'Water',
            'description' => 'Updated without image',
            'is_featured' => false,
            'is_active' => true,
            'sort_order' => 1,
            'image' => '',
        ])
        ->assertRedirect(route('admin.products.index'));

    expect($product->fresh()->image)->toBe('/images/products/drinks.jpg');
});

test('updating a product with a new image stores the upload', function () {
    Storage::fake('public');

    $product = Product::factory()->create([
        'name' => 'Replace Image Product',
        'kind' => 'drink',
        'image' => '/images/products/drinks.jpg',
    ]);

    $this->actingAs($this->admin)
        ->put(route('admin.products.update', $product), [
            'name' => 'Replace Image Product',
            'slug' => $product->slug,
            'kind' => 'drink',
            'tags' => 'Water',
            'description' => 'Updated with image',
            'is_featured' => false,
            'is_active' => true,
            'sort_order' => 1,
            'image' => UploadedFile::fake()->image('replacement.jpg'),
        ])
        ->assertRedirect(route('admin.products.index'));

    $product->refresh();

    expect($product->image)->not->toBe('/images/products/drinks.jpg')
        ->and($product->image)->toStartWith('products/')
        ->and($product->image_url)->toBe('/storage/'.$product->image);

    Storage::disk('public')->assertExists($product->image);
});

test('an empty image string on update does not fail validation', function () {
    $product = Product::factory()->create([
        'kind' => 'drink',
    ]);

    $this->actingAs($this->admin)
        ->put(route('admin.products.update', $product), [
            'name' => $product->name,
            'slug' => $product->slug,
            'kind' => 'drink',
            'is_featured' => false,
            'is_active' => true,
            'sort_order' => 0,
            'image' => '',
        ])
        ->assertSessionDoesntHaveErrors('image')
        ->assertRedirect(route('admin.products.index'));
});

test('create product form receives the next sort order', function () {
    Product::factory()->create([
        'sort_order' => 5,
    ]);

    $this->actingAs($this->admin)
        ->get(route('admin.products.create'))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('admin/products/create')
                ->where('defaultSortOrder', 6)
        );
});

test('a product created without sort order appends after existing rows', function () {
    Product::factory()->create([
        'name' => 'Older Product',
        'kind' => 'drink',
        'sort_order' => 3,
    ]);

    $this->actingAs($this->admin)
        ->post(route('admin.products.store'), [
            'name' => 'Newest Product',
            'kind' => 'chips',
            'is_featured' => false,
            'is_active' => true,
        ])
        ->assertRedirect(route('admin.products.index'));

    $newest = Product::query()->where('name', 'Newest Product')->sole();

    expect($newest->sort_order)->toBe(4);

    $this->actingAs($this->admin)
        ->get(route('admin.products.index'))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->where('products.data.0.name', 'Older Product')
                ->where('products.data.1.name', 'Newest Product')
        );
});
