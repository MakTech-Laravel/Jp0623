<?php

use App\Models\City;
use Database\Seeders\CitySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

test('home page cities come from the seeded cities table', function () {
    $this->seed(CitySeeder::class);

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('cities.title', 'Cities where we manage vending.')
            ->has('cities.items', 30)
            ->where('cities.items.0.city', 'Houston')
            ->where('cities.items.0.state', 'TX')
            ->where('cities.items.0.label', 'Houston vending')
            ->where('cities.items.0.href', '/cities')
        );
});

test('home page shows only the first 30 cities but the cities page shows all', function () {
    foreach (range(1, 35) as $i) {
        City::factory()->create([
            'city' => "City {$i}",
            'state' => 'TX',
            'sort_order' => $i,
        ]);
    }

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('cities.items', 30)
            ->where('cities.items.29.city', 'City 30')
        );

    $this->get(route('cities'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('frontend/cities')
            ->has('cities.items', 35)
        );
});

test('inactive cities are hidden from the public pages', function () {
    City::factory()->create(['city' => 'Visible', 'state' => 'TX', 'sort_order' => 1]);
    City::factory()->inactive()->create(['city' => 'Hidden', 'state' => 'TX', 'sort_order' => 2]);

    $this->get(route('cities'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('cities.items', 1)
            ->where('cities.items.0.city', 'Visible')
        );
});

test('cities page renders without any cities', function () {
    $this->get(route('cities'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->has('cities.items', 0));
});
