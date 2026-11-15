<?php

use App\Models\LocationType;
use Database\Seeders\LocationTypeSeeder;
use Database\Seeders\MachineCategorySeeder;
use Database\Seeders\MachineSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

test('industries index page renders from the seeded location types', function () {
    $this->seed(LocationTypeSeeder::class);

    $this->get(route('industries'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('frontend/industries')
            ->has('site')
            ->has('hero')
            ->where('hero.title', 'Industries we serve')
            ->has('items', 6)
            ->where('items.0.title', 'Business / office')
            ->where('items.0.href', '/industries/business-office')
            ->where('items.5.title', 'Gym')
        );
});

test('industries index hides the form-only and inactive location types', function () {
    $this->seed(LocationTypeSeeder::class);

    LocationType::query()->where('slug', 'gym')->update(['is_active' => false]);

    $this->get(route('industries'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('items', 5)
            ->where('items', fn ($items) => collect($items)->pluck('slug')->doesntContain('other')
                && collect($items)->pluck('slug')->doesntContain('gym'))
        );
});

test('industry show page renders for a known slug', function () {
    $this->seed([LocationTypeSeeder::class, MachineCategorySeeder::class, MachineSeeder::class]);

    $this->get(route('industries.show', 'business-office'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('frontend/industries/show')
            ->has('site')
            ->has('hero')
            ->where('industry.slug', 'business-office')
            ->where('industry.label', 'Business / office')
            ->where('hero.title', 'Vending for business / office')
            ->has('crumb', 3)
            ->where('crumb.1.href', '/industries')
            ->has('benefits.items', 3)
            ->has('relatedMachines.items')
            ->where('hero.cta.href', '#industry-request')
            ->has('requestForm')
        );
});

test('industry show page picks related machines from the database by kind', function () {
    $this->seed([LocationTypeSeeder::class, MachineCategorySeeder::class, MachineSeeder::class]);

    $this->get(route('industries.show', 'apartment-hotel'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('relatedMachines.items', fn ($items) => collect($items)->pluck('id')->contains('ai-cooler'))
        );
});

test('industry show page uses edited location type content', function () {
    LocationType::factory()->create([
        'name' => 'Dealerships',
        'slug' => 'dealerships',
        'description' => 'Custom description from admin.',
    ]);

    $this->get(route('industries.show', 'dealerships'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('industry.label', 'Dealerships')
            ->where('hero.lead', 'Custom description from admin. Free equipment, install and service.')
        );
});

test('industry show page returns 404 for an unknown slug', function () {
    $this->get(route('industries.show', 'not-a-real-industry'))
        ->assertNotFound();
});

test('industry show page returns 404 for a form-only or inactive location type', function () {
    $this->seed(LocationTypeSeeder::class);

    $this->get(route('industries.show', 'other'))->assertNotFound();

    LocationType::query()->where('slug', 'business-office')->update(['is_active' => false]);

    $this->get(route('industries.show', 'business-office'))->assertNotFound();
});
