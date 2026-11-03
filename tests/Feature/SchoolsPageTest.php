<?php

use Inertia\Testing\AssertableInertia as Assert;

test('schools page renders with demo props', function () {
    $this->get(route('schools'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('frontend/schools')
            ->has('site')
            ->where('hero.title', 'School vending')
            ->has('audiences.items', 2)
            ->has('products.items', 5)
            ->has('guide.items', 1)
        );
});

test('header and footer schools links point to the schools page', function () {
    $this->get(route('home'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('site.topNav.3.label', 'Schools')
            ->where('site.topNav.3.href', '/schools')
        );
});
