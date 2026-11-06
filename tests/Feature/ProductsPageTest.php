<?php

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

test('products page renders with database catalog', function () {
    Product::factory()->create([
        'name' => 'Cold Drinks',
        'kind' => 'drink',
        'description' => 'Sodas and waters',
        'sort_order' => 1,
    ]);

    Product::factory()->create([
        'name' => 'Chips',
        'kind' => 'chips',
        'description' => 'Crispy snacks',
        'sort_order' => 2,
    ]);

    Product::factory()->inactive()->create([
        'name' => 'Hidden Product',
        'kind' => 'hidden',
    ]);

    $this->get(route('products'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('frontend/products')
            ->has('site')
            ->has('hero')
            ->where('catalog.disclaimer', 'Availability of products may vary by location')
            ->where('catalog.filters', ['All', 'Cold Drinks', 'Chips'])
            ->has('catalog.items', 2)
            ->where('catalog.items.0.kind', 'drink')
            ->where('catalog.items.0.name', 'Cold Drinks')
            ->where('catalog.items.0.desc', 'Sodas and waters')
        );
});

test('products page lists by sort order then id so newer equal sorts appear later', function () {
    Product::factory()->create([
        'name' => 'First',
        'kind' => 'first',
        'sort_order' => 1,
    ]);

    Product::factory()->create([
        'name' => 'Second',
        'kind' => 'second',
        'sort_order' => 1,
    ]);

    $this->get(route('products'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('catalog.items.0.name', 'First')
            ->where('catalog.items.1.name', 'Second')
        );
});
