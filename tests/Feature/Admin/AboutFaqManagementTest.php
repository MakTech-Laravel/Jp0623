<?php

use App\Enums\RoleEnum;
use App\Models\AboutFaq;
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

test('guests cannot access about faqs', function () {
    $this->get(route('admin.about-faqs.index'))->assertRedirect(route('login'));
});

test('users without permission are forbidden from about faqs', function () {
    $plain = User::factory()->create();

    $this->actingAs($plain)
        ->get(route('admin.about-faqs.index'))
        ->assertForbidden();
});

test('super admin can view the about faqs list', function () {
    AboutFaq::factory()->create(['question' => 'What does your company do?']);

    $this->actingAs($this->admin)
        ->get(route('admin.about-faqs.index'))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('admin/about-faqs/index')
                ->has('faqs.data', 1)
                ->has('filters')
        );
});

test('an about faq can be created', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.about-faqs.store'), [
            'question' => 'How much power do they need?',
            'answer' => 'A standard dedicated 115V outlet.',
            'sort_order' => 1,
            'is_active' => true,
        ])
        ->assertRedirect(route('admin.about-faqs.index'));

    $faq = AboutFaq::query()->where('question', 'How much power do they need?')->sole();

    expect($faq->answer)->toBe('A standard dedicated 115V outlet.')
        ->and($faq->is_active)->toBeTrue();
});

test('creating an about faq validates required fields', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.about-faqs.store'), [
            'question' => '',
        ])
        ->assertSessionHasErrors(['question', 'answer']);
});

test('an about faq can be updated', function () {
    $faq = AboutFaq::factory()->create(['question' => 'Old question?']);

    $this->actingAs($this->admin)
        ->put(route('admin.about-faqs.update', $faq), [
            'question' => 'Updated question?',
            'answer' => 'Updated answer.',
            'sort_order' => 5,
            'is_active' => false,
        ])
        ->assertRedirect(route('admin.about-faqs.index'));

    $faq->refresh();

    expect($faq->question)->toBe('Updated question?')
        ->and($faq->answer)->toBe('Updated answer.')
        ->and($faq->is_active)->toBeFalse();
});

test('an about faq can be deleted', function () {
    $faq = AboutFaq::factory()->create();

    $this->actingAs($this->admin)
        ->delete(route('admin.about-faqs.destroy', $faq))
        ->assertRedirect();

    expect(AboutFaq::query()->whereKey($faq->id)->exists())->toBeFalse();
});
