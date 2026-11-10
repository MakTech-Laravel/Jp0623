<?php

use App\Enums\RoleEnum;
use App\Models\MachineFaq;
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

test('guests cannot access machine faqs', function () {
    $this->get(route('admin.machine-faqs.index'))->assertRedirect(route('login'));
});

test('users without permission are forbidden from machine faqs', function () {
    $plain = User::factory()->create();

    $this->actingAs($plain)
        ->get(route('admin.machine-faqs.index'))
        ->assertForbidden();
});

test('super admin can view the machine faqs list', function () {
    MachineFaq::factory()->create(['question' => 'Are the machines really free?']);

    $this->actingAs($this->admin)
        ->get(route('admin.machine-faqs.index'))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('admin/machine-faqs/index')
                ->has('faqs.data', 1)
                ->has('filters')
        );
});

test('a machine faq can be created', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.machine-faqs.store'), [
            'question' => 'How much power do they need?',
            'answer' => 'A standard dedicated 115V outlet.',
            'sort_order' => 1,
            'is_active' => true,
        ])
        ->assertRedirect(route('admin.machine-faqs.index'));

    $faq = MachineFaq::query()->where('question', 'How much power do they need?')->sole();

    expect($faq->answer)->toBe('A standard dedicated 115V outlet.')
        ->and($faq->is_active)->toBeTrue();
});

test('creating a machine faq validates required fields', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.machine-faqs.store'), [
            'question' => '',
        ])
        ->assertSessionHasErrors(['question', 'answer']);
});

test('a machine faq can be updated', function () {
    $faq = MachineFaq::factory()->create(['question' => 'Old question?']);

    $this->actingAs($this->admin)
        ->put(route('admin.machine-faqs.update', $faq), [
            'question' => 'Updated question?',
            'answer' => 'Updated answer.',
            'sort_order' => 5,
            'is_active' => false,
        ])
        ->assertRedirect(route('admin.machine-faqs.index'));

    $faq->refresh();

    expect($faq->question)->toBe('Updated question?')
        ->and($faq->answer)->toBe('Updated answer.')
        ->and($faq->is_active)->toBeFalse();
});

test('a machine faq can be deleted', function () {
    $faq = MachineFaq::factory()->create();

    $this->actingAs($this->admin)
        ->delete(route('admin.machine-faqs.destroy', $faq))
        ->assertRedirect();

    expect(MachineFaq::query()->whereKey($faq->id)->exists())->toBeFalse();
});
