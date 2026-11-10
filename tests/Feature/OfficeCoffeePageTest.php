<?php

use App\Models\OfficeCoffeeFaq;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

test('office coffee page renders with demo props', function () {
    $this->get(route('office-coffee'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('frontend/office-coffee')
            ->has('site')
            ->has('hero')
            ->where('hero.title', 'Office coffee service')
            ->has('machines.items', 3)
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
