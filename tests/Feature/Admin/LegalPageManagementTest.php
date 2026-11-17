<?php

use App\Enums\RoleEnum;
use App\Models\LegalPage;
use App\Models\User;
use Database\Seeders\LegalPageSeeder;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed([PermissionSeeder::class, RoleSeeder::class, LegalPageSeeder::class]);

    $this->admin = User::factory()->create();
    $this->admin->assignRole(RoleEnum::SUPER_ADMIN->value);
});

test('guests cannot access legal pages', function () {
    $this->get(route('admin.legal-pages.index'))->assertRedirect(route('login'));
});

test('users without permission are forbidden from legal pages', function () {
    $plain = User::factory()->create();

    $this->actingAs($plain)
        ->get(route('admin.legal-pages.index'))
        ->assertForbidden();
});

test('super admin can view the legal pages list', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.legal-pages.index'))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('admin/legal-pages/index')
                ->has('legalPages', 3)
        );
});

test('super admin can open the edit page', function () {
    $legalPage = LegalPage::query()->where('slug', 'terms')->sole();

    $this->actingAs($this->admin)
        ->get(route('admin.legal-pages.edit', $legalPage))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('admin/legal-pages/edit')
                ->where('legalPage.slug', 'terms')
                ->has('legalPage.sections', 11)
        );
});

test('a legal page can be updated and is shown on the public page', function () {
    $legalPage = LegalPage::query()->where('slug', 'privacy')->sole();

    $this->actingAs($this->admin)
        ->put(route('admin.legal-pages.update', $legalPage), [
            'title' => 'Privacy policy',
            'last_updated_on' => '2026-12-05',
            'sections' => [
                ['heading' => 'What we collect', 'body' => "First paragraph.\n\nSecond paragraph."],
                ['heading' => '', 'body' => 'Closing note.'],
            ],
        ])
        ->assertRedirect(route('admin.legal-pages.index'));

    $legalPage->refresh();

    expect($legalPage->sections)->toHaveCount(2)
        ->and($legalPage->sections[1]['heading'])->toBeNull()
        ->and($legalPage->last_updated_on->toDateString())->toBe('2026-12-05');

    $this->get(route('legal.show', 'privacy'))
        ->assertInertia(
            fn (Assert $page) => $page
                ->where('hero.lead', 'Last updated December 5, 2026.')
                ->where('sections.0.heading', 'What we collect')
        );
});

test('updating a legal page validates required fields', function () {
    $legalPage = LegalPage::query()->where('slug', 'privacy')->sole();

    $this->actingAs($this->admin)
        ->put(route('admin.legal-pages.update', $legalPage), [
            'title' => '',
            'sections' => [['heading' => 'No body', 'body' => '']],
        ])
        ->assertSessionHasErrors(['title', 'sections.0.body']);

    $this->actingAs($this->admin)
        ->put(route('admin.legal-pages.update', $legalPage), [
            'title' => 'Privacy policy',
            'sections' => [],
        ])
        ->assertSessionHasErrors(['sections']);
});
