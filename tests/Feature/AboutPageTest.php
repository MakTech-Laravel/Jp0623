<?php

use App\Models\AboutFaq;
use Database\Seeders\AboutFaqSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

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

test('about page lists the seeded active faqs in order', function () {
    $this->seed(AboutFaqSeeder::class);
    AboutFaq::factory()->inactive()->create(['question' => 'Hidden question?']);

    $this->get(route('about'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('faqs.eyebrow', 'FAQ')
            ->has('faqs.items', 5)
            ->where('faqs.items.0.q', 'What does Vending Machine Location Services do?')
            ->has('faqs.items.0.a')
        );
});

test('about page has no faq items when none exist', function () {
    $this->get(route('about'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->has('faqs.items', 0));
});
