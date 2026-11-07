<?php

use Inertia\Testing\AssertableInertia as Assert;

test('schools page renders', function () {
    $this->get(route('schools'))
        ->assertOk()
        ->assertInertia(
            fn(Assert $page) => $page
                ->component('frontend/schools')
                ->has('site')
                ->has('hero')
                ->has('audiences')
                ->has('products')
                ->has('guide')
        );
});

test('resources page renders', function () {
    $this->get(route('resources'))
        ->assertOk()
        ->assertInertia(
            fn(Assert $page) => $page
                ->component('frontend/resources')
                ->has('site')
                ->has('articles.items')
        );
});

test('cities page renders', function () {
    $this->get(route('cities'))
        ->assertOk()
        ->assertInertia(
            fn(Assert $page) => $page
                ->component('frontend/cities')
                ->has('site')
                ->has('cities.items')
        );
});

test('operators page renders', function () {
    $this->get(route('operators'))
        ->assertOk()
        ->assertInertia(
            fn(Assert $page) => $page
                ->component('frontend/operators')
                ->has('site')
                ->has('hero')
                ->has('pricing')
                ->has('steps')
                ->has('tools')
                ->has('faqs')
        );
});

test('legal pages render', function (string $page) {
    $this->get(route('legal.show', $page))
        ->assertOk()
        ->assertInertia(
            fn(Assert $assert) => $assert
                ->component('frontend/legal')
                ->where('page', $page)
                ->has('hero.title')
                ->has('sections')
        );
})->with([
    'privacy',
    'terms',
    'operator-terms',
    'cookies',
]);

test('invalid legal page returns not found', function () {
    $this->get('/legal/not-a-page')->assertNotFound();
});

test('home footer no longer uses hash placeholders for key links', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(
            fn(Assert $page) => $page
                ->where('site.footerColumns.0.links.2.href', '/machines/ai-cooler')
                ->where('site.footerColumns.0.links.4.href', '/schools')
                ->where('site.footerColumns.2.links.2.href', '/resources')
                ->where('site.footerColumns.2.links.3.href', '/cities')
                ->where('site.footerColumns.3.links.0.href', '/operators')
                ->where('site.legalLinks.0.href', '/legal/privacy')
        );
});
