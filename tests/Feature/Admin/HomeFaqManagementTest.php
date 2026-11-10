<?php

use App\Enums\RoleEnum;
use App\Models\HomeFaq;
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

test('guests cannot access home faqs', function () {
    $this->get(route('admin.home-faqs.index'))->assertRedirect(route('login'));
});

test('users without permission are forbidden from home faqs', function () {
    $plain = User::factory()->create();

    $this->actingAs($plain)
        ->get(route('admin.home-faqs.index'))
        ->assertForbidden();
});

test('super admin can view the home faqs list', function () {
    HomeFaq::factory()->create(['question' => 'How does it work?']);

    $this->actingAs($this->admin)
        ->get(route('admin.home-faqs.index'))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('admin/home-faqs/index')
                ->has('faqs.data', 1)
                ->has('filters')
        );
});

test('a home faq can be created', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.home-faqs.store'), [
            'question' => 'How do I get started?',
            'answer' => 'Fill out the free request form.',
            'sort_order' => 1,
            'is_active' => true,
        ])
        ->assertRedirect(route('admin.home-faqs.index'));

    $faq = HomeFaq::query()->where('question', 'How do I get started?')->sole();

    expect($faq->answer)->toBe('Fill out the free request form.')
        ->and($faq->is_active)->toBeTrue();
});

test('creating a home faq validates required fields', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.home-faqs.store'), [
            'question' => '',
        ])
        ->assertSessionHasErrors(['question', 'answer']);
});

test('a home faq can be updated', function () {
    $faq = HomeFaq::factory()->create(['question' => 'Old question?']);

    $this->actingAs($this->admin)
        ->put(route('admin.home-faqs.update', $faq), [
            'question' => 'Updated question?',
            'answer' => 'Updated answer.',
            'sort_order' => 5,
            'is_active' => false,
        ])
        ->assertRedirect(route('admin.home-faqs.index'));

    $faq->refresh();

    expect($faq->question)->toBe('Updated question?')
        ->and($faq->answer)->toBe('Updated answer.')
        ->and($faq->is_active)->toBeFalse();
});

test('a home faq can be deleted', function () {
    $faq = HomeFaq::factory()->create();

    $this->actingAs($this->admin)
        ->delete(route('admin.home-faqs.destroy', $faq))
        ->assertRedirect();

    expect(HomeFaq::query()->whereKey($faq->id)->exists())->toBeFalse();
});
