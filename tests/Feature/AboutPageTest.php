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
            ->has('story.values', 2)
            ->where('story.values', [
                'No cost to the location',
                'Local operators who respond quickly',
            ])
            ->has('story.kpis', 3)
            ->where('story.kpis.0.label', 'States covered')
            ->where('story.kpis.1.label', 'Cities with local pages')
            ->where('story.kpis.2.label', 'Operators in network')
            ->has('cta.primary')
            ->where('cta.primary.href', '/#request-sec')
        );
});
