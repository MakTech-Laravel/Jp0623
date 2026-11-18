<?php

use App\Enums\RoleEnum;
use App\Models\Review;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed([PermissionSeeder::class, RoleSeeder::class]);

    $this->admin = User::factory()->create();
    $this->admin->assignRole(RoleEnum::SUPER_ADMIN->value);
});

test('guests cannot access reviews', function () {
    $this->get(route('admin.reviews.index'))->assertRedirect(route('login'));
});

test('users without permission are forbidden from reviews', function () {
    $plain = User::factory()->create();

    $this->actingAs($plain)
        ->get(route('admin.reviews.index'))
        ->assertForbidden();
});

test('super admin can view the reviews list', function () {
    Review::factory()->create(['role' => 'Facilities Manager']);

    $this->actingAs($this->admin)
        ->get(route('admin.reviews.index'))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('admin/reviews/index')
                ->has('reviews.data', 1)
                ->has('filters')
        );
});

test('a review can be created', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.reviews.store'), [
            'quote' => 'Great service from start to finish.',
            'role' => 'Office Manager',
            'org' => 'Law firm, NY',
            'rating' => 4,
            'sort_order' => 1,
            'is_active' => true,
        ])
        ->assertRedirect(route('admin.reviews.index'));

    $review = Review::query()->where('role', 'Office Manager')->sole();

    expect($review->quote)->toBe('Great service from start to finish.')
        ->and($review->org)->toBe('Law firm, NY')
        ->and($review->rating)->toBe(4)
        ->and($review->is_active)->toBeTrue();
});

test('creating a review validates required fields and rating bounds', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.reviews.store'), ['role' => ''])
        ->assertSessionHasErrors(['quote', 'role', 'org', 'rating']);

    $this->actingAs($this->admin)
        ->post(route('admin.reviews.store'), [
            'quote' => 'Text',
            'role' => 'Role',
            'org' => 'Org',
            'rating' => 6,
        ])
        ->assertSessionHasErrors(['rating']);
});

test('a review can be updated', function () {
    $review = Review::factory()->create(['role' => 'Old role']);

    $this->actingAs($this->admin)
        ->put(route('admin.reviews.update', $review), [
            'quote' => 'Updated quote.',
            'role' => 'Updated role',
            'org' => 'Updated org',
            'rating' => 3,
            'sort_order' => 5,
            'is_active' => false,
        ])
        ->assertRedirect(route('admin.reviews.index'));

    $review->refresh();

    expect($review->role)->toBe('Updated role')
        ->and($review->quote)->toBe('Updated quote.')
        ->and($review->rating)->toBe(3)
        ->and($review->is_active)->toBeFalse();
});

test('a review can be deleted', function () {
    $review = Review::factory()->create();

    $this->actingAs($this->admin)
        ->delete(route('admin.reviews.destroy', $review))
        ->assertRedirect();

    expect(Review::query()->whereKey($review->id)->exists())->toBeFalse();
});
