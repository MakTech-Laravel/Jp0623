<?php

use Inertia\Testing\AssertableInertia as Assert;

test('cities index page renders with demo props', function () {
    $this->get(route('cities'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('frontend/cities')
            ->has('site')
            ->where('hero.title', 'Cities we serve')
            ->has('items', 30)
            ->where('items.0.href', '/cities/houston')
        );
});

test('city show page renders for a known slug', function () {
    $this->get(route('cities.show', 'houston'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('frontend/cities/show')
            ->has('site')
            ->where('hero.title', 'Vending services in Houston')
            ->where('hero.eyebrow', 'Houston, TX')
            ->where('city.slug', 'houston')
            ->has('crumb', 3)
            ->where('crumb.1.href', '/cities')
            ->has('benefits.items', 3)
            ->has('requestForm')
            ->has('faqs.items', 3)
            ->has('nearby', 5)
            ->where('nearby.0.label', 'Dallas')
        );
});

test('city show page returns 404 for an unknown slug', function () {
    $this->get(route('cities.show', 'not-a-real-city'))
        ->assertNotFound();
});

test('header and footer link to the cities page', function () {
    $this->get(route('home'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('site.topNav.4.label', 'Cities')
            ->where('site.topNav.4.href', '/cities')
        );
});
