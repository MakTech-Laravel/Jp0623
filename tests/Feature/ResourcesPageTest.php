<?php

use Inertia\Testing\AssertableInertia as Assert;

test('resources hub renders with demo props', function () {
    $this->get(route('resources'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('frontend/resources')
            ->has('site')
            ->where('hero.title', 'Vending & office coffee resource hub')
            ->has('filters', 7)
            ->where('filters.0', 'All')
            ->has('items', 6)
            ->where('items.0.href', '/resources/multi-site')
            ->missing('items.0.body')
        );
});

test('article page renders for a known slug', function () {
    $this->get(route('resources.show', 'school-rules'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('frontend/resources/show')
            ->has('site')
            ->has('crumb', 3)
            ->where('crumb.1.href', '/resources')
            ->where('article.id', 'school-rules')
            ->has('article.body', 3)
            ->where('cta.button.href', '/#request-sec')
            ->has('related', 3)
            ->where('related.0.id', 'multi-site')
        );
});

test('article page returns 404 for an unknown slug', function () {
    $this->get(route('resources.show', 'not-a-real-article'))
        ->assertNotFound();
});

test('header, home and schools link to the resources hub', function () {
    $this->get(route('home'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('site.topNav.5.label', 'Resources')
            ->where('site.topNav.5.href', '/resources')
            ->where('articles.link.href', '/resources')
            ->where('articles.items.0.href', '/resources/multi-site')
        );

    $this->get(route('schools'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('guide.items.0.href', '/resources/school-rules')
        );
});
