<?php

use App\Models\HomeFaq;
use App\Models\LocationType;
use Database\Seeders\ArticleSeeder;
use Database\Seeders\HomeFaqSeeder;
use App\Models\Machine;
use App\Models\MachineCategory;
use Database\Seeders\LocationTypeSeeder;
use Database\Seeders\MachineCategorySeeder;
use Database\Seeders\MachineSeeder;
use Database\Seeders\ProductSeeder;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

test('home page renders the frontend homepage with demo props', function () {
    $this->seed([
        HomeFaqSeeder::class,
        LocationTypeSeeder::class,
        MachineCategorySeeder::class,
        MachineSeeder::class,
        ProductSeeder::class,
        ArticleSeeder::class,
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(
            fn(Assert $page) => $page
                ->component('frontend/home')
                ->has('site')
                ->has('site.mainNav')
                ->has('site.footerColumns')
                ->has('hero')
                ->has('howItWorks.steps', 3)
                ->has('services.items', 3)
                ->has('featuredMachines.items', 3)
                ->has('products.items', 5)
                ->has('requestForm')
                ->has('requestForm.yesNoOptions', 2)
                ->has('requestForm.locationTypes', 7)
                ->where('requestForm.locationTypes.0', 'Business / office')
                ->where('requestForm.locationTypes.6', 'Other')
                ->missing('site.phone')
                ->has('reviews.items', 6)
                ->has('industries.items', 6)
                ->where('industries.items.0.label', 'Business / office')
                ->where('industries.items.0.href', '/industries/business-office')
                ->where('industries.items', fn ($items) => collect($items)->pluck('label')->doesntContain('Other'))
                ->has('faqs.items', 12)
                ->where('faqs.items.0.q', 'What does a vending management company do?')
                ->has('articles.items', 3)
                ->where('articles.items.0.href', '/resources/multi-site')
                ->has('cities.items', 30)
        );
});

test('home page location types come from the database and hide inactive items', function () {
    LocationType::factory()->create([
        'name' => 'Active location',
        'sort_order' => 1,
        'is_active' => true,
    ]);

    LocationType::factory()->inactive()->create([
        'name' => 'Hidden location',
        'sort_order' => 2,
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(
            fn(Assert $page) => $page
                ->has('requestForm.locationTypes', 1)
                ->where('requestForm.locationTypes.0', 'Active location')
        );
});

test('home page featured machines come from featured active machines', function () {
    $category = MachineCategory::factory()->create();

    Machine::factory()->featured()->create([
        'machine_category_id' => $category->id,
        'name' => 'Featured One',
        'slug' => 'featured-one',
        'sort_order' => 1,
    ]);
    Machine::factory()->create([
        'machine_category_id' => $category->id,
        'name' => 'Not Featured',
        'slug' => 'not-featured',
        'sort_order' => 2,
    ]);
    Machine::factory()->featured()->inactive()->create([
        'machine_category_id' => $category->id,
        'slug' => 'featured-hidden',
        'sort_order' => 3,
    ]);
    Machine::factory()->featured()->count(3)->sequence(
        ['slug' => 'f-a', 'sort_order' => 4],
        ['slug' => 'f-b', 'sort_order' => 5],
        ['slug' => 'f-c', 'sort_order' => 6],
    )->create(['machine_category_id' => $category->id]);

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->has('featuredMachines.items', 3)
                ->where('featuredMachines.items.0.id', 'featured-one')
                ->where('featuredMachines.items.1.id', 'f-a')
                ->where('featuredMachines.link.label', 'See all 5 machines →')
        );
});

test('home page products teaser comes from featured active products', function () {
    Product::factory()->featured()->create(['name' => 'Chips', 'kind' => 'chips', 'slug' => 'chips', 'sort_order' => 2]);
    Product::factory()->featured()->create(['name' => 'Drinks', 'kind' => 'drink', 'slug' => 'drink', 'sort_order' => 1]);
    Product::factory()->create(['name' => 'Plain', 'kind' => 'plain', 'slug' => 'plain', 'sort_order' => 3]);
    Product::factory()->featured()->inactive()->create(['kind' => 'hidden', 'slug' => 'hidden', 'sort_order' => 4]);

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->has('products.items', 2)
                ->where('products.items.0.name', 'Drinks')
                ->where('products.items.1.name', 'Chips')
        );
});

test('home page faqs come from the database and hide inactive items', function () {
    HomeFaq::factory()->create([
        'question' => 'Active FAQ?',
        'answer' => 'Yes.',
        'sort_order' => 1,
        'is_active' => true,
    ]);

    HomeFaq::factory()->inactive()->create([
        'question' => 'Hidden FAQ?',
        'answer' => 'No.',
        'sort_order' => 2,
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(
            fn(Assert $page) => $page
                ->has('faqs.items', 1)
                ->where('faqs.items.0.q', 'Active FAQ?')
                ->where('faqs.items.0.a', 'Yes.')
        );
});
