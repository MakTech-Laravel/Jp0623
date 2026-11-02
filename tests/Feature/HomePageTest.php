<?php

use Inertia\Testing\AssertableInertia as Assert;

test('home page renders the frontend homepage with demo props', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('frontend/home')
            ->has('site')
            ->has('site.mainNav')
            ->has('site.footerColumns')
            ->has('hero')
            ->has('howItWorks.steps', 3)
            ->has('services.items', 4)
            ->has('featuredMachines.items', 3)
            ->has('products.items', 5)
            ->has('requestForm')
            ->has('reviews.items', 6)
            ->has('industries.items', 10)
            ->has('faqs.items', 12)
            ->has('articles.items', 3)
            ->has('cities.items', 30)
        );
});
