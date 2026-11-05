<?php

use Inertia\Testing\AssertableInertia as Assert;

test('contact page renders with demo props', function () {
    $this->get(route('contact'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('frontend/contact')
            ->has('site')
            ->has('hero')
            ->where('hero.title', 'Contact us')
            ->has('panel')
            ->where('panel.title', 'Talk to our team')
            ->where('panel.email', 'hello@vendingmachinelocationsforsale.com')
            ->missing('panel.phone')
            ->has('form.topics', 4)
            ->has('form.successMessage')
        );
});
