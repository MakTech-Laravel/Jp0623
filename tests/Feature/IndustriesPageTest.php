<?php

use Inertia\Testing\AssertableInertia as Assert;

test('industries index page renders with demo props', function () {
    $this->get(route('industries'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('frontend/industries')
            ->has('site')
            ->has('hero')
            ->where('hero.title', 'Industries we serve')
            ->has('items', 10)
            ->where('items.0.href', '/industries/offices')
        );
});

test('industry show page renders for a known slug', function () {
    $this->get(route('industries.show', 'offices'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('frontend/industries/show')
            ->has('site')
            ->has('hero')
            ->where('industry.slug', 'offices')
            ->where('industry.label', 'Offices')
            ->has('crumb', 3)
            ->where('crumb.1.href', '/industries')
            ->has('benefits.items', 3)
            ->has('relatedMachines.items')
            ->where('hero.cta.href', '#industry-request')
            ->has('requestForm')
        );
});

test('industry show page returns 404 for an unknown slug', function () {
    $this->get(route('industries.show', 'not-a-real-industry'))
        ->assertNotFound();
});
