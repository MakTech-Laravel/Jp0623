<?php

use Inertia\Testing\AssertableInertia as Assert;

test('atm page renders with demo props', function () {
    $this->get(route('atm'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('frontend/atm')
            ->has('site')
            ->where('hero.title', 'ATM machines')
            ->has('benefits.items', 4)
            ->has('goodFit.items', 7)
            ->has('request.successMessage')
        );
});

test('header ATM link points to the atm page', function () {
    $this->get(route('home'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('site.topNav.2.label', 'ATM')
            ->where('site.topNav.2.href', '/atm')
        );
});
