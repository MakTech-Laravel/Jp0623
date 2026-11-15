<?php

use App\Models\Product;
use Database\Seeders\ProductSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

test('schools page picks come from the seeded products', function () {
    $this->seed(ProductSeeder::class);

    $this->get(route('schools'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('frontend/schools')
            ->where('products.title', 'School-friendly picks.')
            ->has('products.items', 4)
            ->where('products.items', fn ($items) => collect($items)->pluck('title')->all() === [
                'Beverages',
                'Nuts & snack bars',
                'Healthy choices',
                'Fresh food',
            ])
        );
});

test('schools page picks hide inactive products and use edited names', function () {
    $this->seed(ProductSeeder::class);

    Product::query()->where('kind', 'food')->update(['is_active' => false]);
    Product::query()->where('kind', 'healthy')->update(['name' => 'Better choices']);

    $this->get(route('schools'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('products.items', 3)
            ->where('products.items', fn ($items) => collect($items)->pluck('title')->contains('Better choices')
                && ! collect($items)->pluck('title')->contains('Fresh food'))
        );
});

test('schools page renders without any products', function () {
    $this->get(route('schools'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->has('products.items', 0));
});
