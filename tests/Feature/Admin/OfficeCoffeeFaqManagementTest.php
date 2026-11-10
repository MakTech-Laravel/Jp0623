<?php

use App\Enums\RoleEnum;
use App\Models\OfficeCoffeeFaq;
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

test('guests cannot access office coffee faqs', function () {
    $this->get(route('admin.office-coffee-faqs.index'))->assertRedirect(route('login'));
});

test('users without permission are forbidden from office coffee faqs', function () {
    $plain = User::factory()->create();

    $this->actingAs($plain)
        ->get(route('admin.office-coffee-faqs.index'))
        ->assertForbidden();
});

test('super admin can view the office coffee faqs list', function () {
    OfficeCoffeeFaq::factory()->create(['question' => 'Is coffee service free?']);

    $this->actingAs($this->admin)
        ->get(route('admin.office-coffee-faqs.index'))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('admin/office-coffee-faqs/index')
                ->has('faqs.data', 1)
                ->has('filters')
        );
});

test('an office coffee faq can be created', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.office-coffee-faqs.store'), [
            'question' => 'Do you restock supplies?',
            'answer' => 'Yes, on a schedule that fits your office.',
            'sort_order' => 1,
            'is_active' => true,
        ])
        ->assertRedirect(route('admin.office-coffee-faqs.index'));

    $faq = OfficeCoffeeFaq::query()->where('question', 'Do you restock supplies?')->sole();

    expect($faq->answer)->toBe('Yes, on a schedule that fits your office.')
        ->and($faq->is_active)->toBeTrue();
});

test('creating an office coffee faq validates required fields', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.office-coffee-faqs.store'), [
            'question' => '',
        ])
        ->assertSessionHasErrors(['question', 'answer']);
});

test('an office coffee faq can be updated', function () {
    $faq = OfficeCoffeeFaq::factory()->create(['question' => 'Old question?']);

    $this->actingAs($this->admin)
        ->put(route('admin.office-coffee-faqs.update', $faq), [
            'question' => 'Updated question?',
            'answer' => 'Updated answer.',
            'sort_order' => 5,
            'is_active' => false,
        ])
        ->assertRedirect(route('admin.office-coffee-faqs.index'));

    $faq->refresh();

    expect($faq->question)->toBe('Updated question?')
        ->and($faq->answer)->toBe('Updated answer.')
        ->and($faq->is_active)->toBeFalse();
});

test('an office coffee faq can be deleted', function () {
    $faq = OfficeCoffeeFaq::factory()->create();

    $this->actingAs($this->admin)
        ->delete(route('admin.office-coffee-faqs.destroy', $faq))
        ->assertRedirect();

    expect(OfficeCoffeeFaq::query()->whereKey($faq->id)->exists())->toBeFalse();
});
