<?php

use Inertia\Testing\AssertableInertia as Assert;

test('machines page renders with demo props', function () {
    $this->get(route('machines'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('frontend/machines')
            ->has('site')
            ->has('hero')
            ->has('lineup.filters')
            ->where(
                'lineup.text',
                'Availability of machines and or micro markets may vary by region. Multiple machines can be placed at locations with more visitors/employees.',
            )
            ->has('lineup.items', 7)
            ->where('lineup.items.0.href', '/machines/combo')
            ->has('tech.items', 4)
            ->has('industries.items', 10)
            ->has('faqs.items', 4)
        );
});
