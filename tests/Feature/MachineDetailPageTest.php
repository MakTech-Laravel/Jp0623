<?php

use Inertia\Testing\AssertableInertia as Assert;

test('machine detail page renders with demo props', function () {
    $this->get(route('machines.show', 'healthy'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('frontend/machines/show')
            ->has('site')
            ->where('machine.id', 'healthy')
            ->where('status', 'Available nationwide')
            ->has('specs', 5)
            ->has('checks', 4)
            ->has('related.items', 3)
            ->where('related.title', 'Other machines you might like.')
            ->where('cta.label', 'Request this machine')
            ->where('cta.href', '/#request-sec')
        );
});

test('unknown machine detail returns 404', function () {
    $this->get(route('machines.show', 'not-a-machine'))
        ->assertNotFound();
});
