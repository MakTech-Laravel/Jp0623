<?php

use App\Enums\RoleEnum;
use App\Models\Article;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed([PermissionSeeder::class, RoleSeeder::class]);

    $this->admin = User::factory()->create();
    $this->admin->assignRole(RoleEnum::SUPER_ADMIN->value);
});

test('guests cannot access articles', function () {
    $this->get(route('admin.articles.index'))->assertRedirect(route('login'));
});

test('users without permission are forbidden from articles', function () {
    $plain = User::factory()->create();

    $this->actingAs($plain)
        ->get(route('admin.articles.index'))
        ->assertForbidden();
});

test('super admin can view the articles list', function () {
    Article::factory()->create(['title' => 'A guide']);

    $this->actingAs($this->admin)
        ->get(route('admin.articles.index'))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('admin/articles/index')
                ->has('articles.data', 1)
                ->has('filters')
        );
});

test('an article can be created and gets a slug from the title', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.articles.store'), [
            'category' => 'Vending management',
            'title' => 'Choosing a vendor',
            'excerpt' => 'A short summary.',
            'body' => "First paragraph.\n\nSecond paragraph.",
            'sort_order' => 3,
            'is_active' => true,
        ])
        ->assertRedirect(route('admin.articles.index'));

    $article = Article::query()->where('title', 'Choosing a vendor')->sole();

    expect($article->slug)->toBe('choosing-a-vendor')
        ->and($article->sort_order)->toBe(3)
        ->and($article->is_active)->toBeTrue();
});

test('duplicate titles get a unique suffixed slug', function () {
    Article::factory()->create(['slug' => 'same-title']);

    $this->actingAs($this->admin)
        ->post(route('admin.articles.store'), [
            'category' => 'Healthy vending',
            'title' => 'Same title',
        ])
        ->assertRedirect(route('admin.articles.index'));

    expect(Article::query()->where('title', 'Same title')->sole()->slug)->toBe('same-title-1');
});

test('an article can be created with an image', function () {
    Storage::fake('public');

    $this->actingAs($this->admin)
        ->post(route('admin.articles.store'), [
            'category' => 'Healthy vending',
            'title' => 'With cover',
            'image' => UploadedFile::fake()->image('cover.jpg'),
        ])
        ->assertRedirect(route('admin.articles.index'));

    $article = Article::query()->where('title', 'With cover')->sole();

    expect($article->image)->not->toBeNull();
    Storage::disk('public')->assertExists($article->image);
});

test('creating an article validates required fields', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.articles.store'), [
            'title' => '',
            'category' => '',
        ])
        ->assertSessionHasErrors(['title', 'category']);
});

test('an article can be updated and keeps its slug', function () {
    $article = Article::factory()->create(['title' => 'Old title', 'slug' => 'old-title']);

    $this->actingAs($this->admin)
        ->put(route('admin.articles.update', $article), [
            'category' => 'Smart & AI vending',
            'title' => 'Updated title',
            'slug' => 'old-title',
            'sort_order' => 5,
            'is_active' => false,
        ])
        ->assertRedirect(route('admin.articles.index'));

    $article->refresh();

    expect($article->title)->toBe('Updated title')
        ->and($article->slug)->toBe('old-title')
        ->and($article->sort_order)->toBe(5)
        ->and($article->is_active)->toBeFalse();
});

test('an article slug must be unique on update', function () {
    Article::factory()->create(['slug' => 'taken']);
    $article = Article::factory()->create(['slug' => 'mine']);

    $this->actingAs($this->admin)
        ->put(route('admin.articles.update', $article), [
            'category' => 'Healthy vending',
            'title' => 'Mine',
            'slug' => 'taken',
        ])
        ->assertSessionHasErrors(['slug']);
});

test('an article can be deleted and its stored image is removed', function () {
    Storage::fake('public');
    Storage::disk('public')->put('articles/test.jpg', 'x');

    $article = Article::factory()->create(['image' => 'articles/test.jpg']);

    $this->actingAs($this->admin)
        ->delete(route('admin.articles.destroy', $article))
        ->assertRedirect();

    expect(Article::query()->whereKey($article->id)->exists())->toBeFalse();
    Storage::disk('public')->assertMissing('articles/test.jpg');
});
