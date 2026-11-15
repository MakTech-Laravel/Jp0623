<?php

use App\Models\Machine;
use App\Models\Product;
use Database\Seeders\MachineCategorySeeder;
use Database\Seeders\MachineSeeder;
use Database\Seeders\ProductSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

test('micro markets page renders with demo props', function () {
    $this->seed([MachineCategorySeeder::class, MachineSeeder::class, ProductSeeder::class]);

    $this->get(route('micro-markets'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('frontend/micro-markets')
            ->has('site')
            ->has('hero')
            ->where('hero.image', '/images/machines/micro-market.jpg')
            ->has('howItWorks.steps', 3)
            ->has('equipment.items', 4)
            ->where('offerings.title', 'From breakfast to late shift.')
            ->has('offerings.items', 5)
            ->where('compare.title', 'Micro markets & Vendera coolers: we offer both.')
            ->has('compare.items', 2)
            ->where('compare.items.0.href', '/machines/micro-market')
            ->where('compare.items.1.href', '/machines/ai-cooler')
            ->has('faqs.items', 4)
        );
});

test('micro markets offerings come from active products of the listed kinds', function () {
    Product::factory()->create(['kind' => 'food', 'name' => 'Fresh meals', 'sort_order' => 2]);
    Product::factory()->create(['kind' => 'coffee', 'name' => 'Coffee', 'sort_order' => 1]);
    Product::factory()->inactive()->create(['kind' => 'drink', 'name' => 'Hidden drinks']);
    Product::factory()->create(['kind' => 'chips', 'name' => 'Chips (not listed)']);

    $this->get(route('micro-markets'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('offerings.items', 2)
            ->where('offerings.items.0.name', 'Coffee')
            ->where('offerings.items.1.name', 'Fresh meals')
        );
});

test('micro markets compare cards come from machines and hide inactive ones', function () {
    $this->seed([MachineCategorySeeder::class, MachineSeeder::class]);

    Machine::query()->where('slug', 'ai-cooler')->update(['is_active' => false]);
    Machine::query()->where('slug', 'micro-market')->update(['name' => 'Edited market name']);

    $this->get(route('micro-markets'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('compare.items', 1)
            ->where('compare.items.0.title', 'Edited market name')
            ->where('compare.items.0.tag', 'Sites with 100+ people')
        );
});
