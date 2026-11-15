<?php

use App\Models\CoffeeMachine;
use App\Models\OfficeCoffeeFaq;
use App\Models\CoffeeType;
use Database\Seeders\CoffeeMachineSeeder;
use Database\Seeders\CoffeeTypeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

test('office coffee page renders with demo props', function () {
    $this->seed([CoffeeMachineSeeder::class, CoffeeTypeSeeder::class]);

    $this->get(route('office-coffee'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('frontend/office-coffee')
            ->has('site')
            ->has('hero')
            ->where('hero.title', 'Office coffee service')
            ->where('machines.title', 'Equipment for every office.')
            ->has('machines.items', 3)
            ->where('machines.items.0.title', 'Single-cup brewer')
            ->where('machines.items.0.href', '#coffee-form')
            ->has('types.items', 9)
            ->where('types.items.0.label', 'Medium roast')
            ->where('types.items.0.href', '#coffee-form')
            ->has('service.items', 3)
            ->where('service.items.0.href', '#coffee-form')
            ->has('requestForm')
            ->has('faqs.items', 0)
            ->where('faqs.eyebrow', 'FAQ')
            ->where('faqs.title', 'Office coffee questions.')
        );
});

test('office coffee page machines come from the database and hide inactive items', function () {
    CoffeeMachine::factory()->create([
        'title' => 'Second brewer',
        'text' => 'Second.',
        'tag' => '10 people',
        'sort_order' => 2,
    ]);

    CoffeeMachine::factory()->create([
        'title' => 'First brewer',
        'text' => 'First.',
        'tag' => '5 people',
        'sort_order' => 1,
    ]);

    CoffeeMachine::factory()->inactive()->create([
        'title' => 'Hidden brewer',
        'sort_order' => 3,
    ]);

    $this->get(route('office-coffee'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('machines.items', 2)
            ->where('machines.items.0.title', 'First brewer')
            ->where('machines.items.0.tag', '5 people')
            ->where('machines.items.1.title', 'Second brewer')
        );
});

test('office coffee page types come from the database and hide inactive items', function () {
    CoffeeType::factory()->create(['label' => 'Second type', 'sort_order' => 2]);
    CoffeeType::factory()->create(['label' => 'First type', 'sort_order' => 1]);
    CoffeeType::factory()->inactive()->create(['label' => 'Hidden type', 'sort_order' => 3]);

    $this->get(route('office-coffee'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('types.items', 2)
            ->where('types.items.0.label', 'First type')
            ->where('types.items.0.href', '#coffee-form')
            ->where('types.items.1.label', 'Second type')
        );
});

test('office coffee page faqs come from the database and hide inactive items', function () {
    OfficeCoffeeFaq::factory()->create([
        'question' => 'Active coffee FAQ?',
        'answer' => 'Yes.',
        'sort_order' => 1,
        'is_active' => true,
    ]);

    OfficeCoffeeFaq::factory()->inactive()->create([
        'question' => 'Hidden coffee FAQ?',
        'answer' => 'No.',
        'sort_order' => 2,
    ]);

    $this->get(route('office-coffee'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('faqs.items', 1)
            ->where('faqs.items.0.q', 'Active coffee FAQ?')
            ->where('faqs.items.0.a', 'Yes.')
        );
});
