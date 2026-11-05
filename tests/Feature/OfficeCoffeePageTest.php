<?php

use Inertia\Testing\AssertableInertia as Assert;

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
        );
});
