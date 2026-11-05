<?php

use Inertia\Testing\AssertableInertia as Assert;

test('about page renders with demo props', function () {
    $this->get(route('about'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('frontend/about')
            ->has('site')
            ->has('hero')
            ->where('hero.title', 'About us')
            ->has('story')
            ->has('story.values', 3)
            ->has('story.kpis', 4)
            ->has('cta.primary')
            ->where('cta.primary.href', '/#request-sec')
        );
});
