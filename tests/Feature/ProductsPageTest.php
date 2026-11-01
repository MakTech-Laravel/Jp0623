<?php

use Inertia\Testing\AssertableInertia as Assert;

test('products page renders with demo props', function () {
    $this->get(route('products'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('frontend/products')
            ->has('site')
            ->has('hero')
            ->has('catalog.filters')
            ->has('catalog.items', 10)
        );
});
