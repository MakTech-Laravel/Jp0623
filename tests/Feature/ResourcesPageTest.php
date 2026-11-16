<?php

use App\Models\Article;
use Database\Seeders\ArticleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

test('resources page lists seeded articles with detail links', function () {
    $this->seed(ArticleSeeder::class);

    $this->get(route('resources'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('frontend/resources')
            ->has('articles.items', 3)
            ->where('articles.items.0.id', 'multi-site')
            ->where('articles.items.0.href', '/resources/multi-site')
        );
});

test('resources page hides inactive articles', function () {
    Article::factory()->create(['slug' => 'visible', 'sort_order' => 1]);
    Article::factory()->inactive()->create(['slug' => 'hidden', 'sort_order' => 2]);

    $this->get(route('resources'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('articles.items', 1)
            ->where('articles.items.0.id', 'visible')
        );
});

test('article detail page renders body paragraphs and related articles', function () {
    $this->seed(ArticleSeeder::class);

    $this->get(route('resources.show', 'ai-vs-market'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('frontend/resources/show')
            ->has('site')
            ->where('article.slug', 'ai-vs-market')
            ->where('article.title', 'Vendera coolers vs. micro markets: which one fits?')
            ->has('article.paragraphs', 3)
            ->has('related', 2)
            ->where('related', fn ($items) => collect($items)->pluck('id')->doesntContain('ai-vs-market'))
        );
});

test('article detail splits the body on blank lines', function () {
    Article::factory()->create([
        'slug' => 'split-me',
        'body' => "One.\n\n\nTwo.\r\n\r\nThree.",
    ]);

    $this->get(route('resources.show', 'split-me'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('article.paragraphs', ['One.', 'Two.', 'Three.'])
        );
});

test('article detail returns 404 for unknown or inactive slugs', function () {
    Article::factory()->inactive()->create(['slug' => 'hidden']);

    $this->get(route('resources.show', 'nope'))->assertNotFound();
    $this->get(route('resources.show', 'hidden'))->assertNotFound();
});

test('schools guide card uses the third active article', function () {
    $this->seed(ArticleSeeder::class);

    $this->get(route('schools'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('guide.item.id', 'healthy-mix')
            ->where('guide.item.href', '/resources/healthy-mix')
        );
});

test('schools guide card is empty when there are no articles', function () {
    $this->get(route('schools'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->where('guide.item', null));
});
