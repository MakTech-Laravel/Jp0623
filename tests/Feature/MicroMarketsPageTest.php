<?php

use Inertia\Testing\AssertableInertia as Assert;

test('micro markets page renders with demo props', function () {
    $this->get(route('micro-markets'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('frontend/micro-markets')
            ->has('site')
            ->has('hero')
            ->where('hero.art', 'market')
            ->has('howItWorks.steps', 3)
            ->has('equipment.items', 4)
            ->has('offerings.items', 5)
            ->has('compare.items', 2)
            ->has('faqs.items', 4)
        );
});
