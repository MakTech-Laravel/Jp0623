<?php

use App\Models\Machine;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('machine image_url uses a relative storage path for uploads', function () {
    $machine = Machine::factory()->create([
        'image' => 'machines/test.png',
    ]);

    expect($machine->image_url)->toBe('/storage/machines/test.png');
});

test('machine image_url keeps seeded public paths', function () {
    $machine = Machine::factory()->create([
        'image' => '/images/machines/foo.jpg',
    ]);

    expect($machine->image_url)->toBe('/images/machines/foo.jpg');
});

test('product image_url uses a relative storage path for uploads', function () {
    $product = Product::factory()->create([
        'image' => 'products/test.png',
    ]);

    expect($product->image_url)->toBe('/storage/products/test.png');
});

test('product image_url keeps seeded public paths', function () {
    $product = Product::factory()->create([
        'image' => '/images/products/foo.jpg',
    ]);

    expect($product->image_url)->toBe('/images/products/foo.jpg');
});
