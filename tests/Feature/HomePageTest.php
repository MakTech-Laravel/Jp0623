<?php

use App\Models\HomeFaq;
use App\Models\LocationType;
use Database\Seeders\HomeFaqSeeder;
use Database\Seeders\LocationTypeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

test('home page renders the frontend homepage with demo props', function () {
    $this->seed([HomeFaqSeeder::class, LocationTypeSeeder::class]);

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
