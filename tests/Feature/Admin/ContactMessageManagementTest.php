<?php

use App\Enums\RoleEnum;
use App\Models\ContactMessage;
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

test('guests cannot access contact messages', function () {
    $this->get(route('admin.contact-messages.index'))
        ->assertRedirect(route('login'));
});

test('users without permission are forbidden from contact messages', function () {
    $plain = User::factory()->create();

    $this->actingAs($plain)
        ->get(route('admin.contact-messages.index'))
        ->assertForbidden();
});

test('super admin can view the contact messages list', function () {
    ContactMessage::factory()->create([
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
    ]);

    $this->actingAs($this->admin)
        ->get(route('admin.contact-messages.index'))
        ->assertOk()
        ->assertInertia(
            fn(Assert $page) => $page
                ->component('admin/contact-messages/index')
                ->has('messages.data', 1)
                ->where('messages.data.0.name', 'Jane Doe')
                ->where('messages.data.0.email', 'jane@example.com')
                ->has('filters.status')
        );
});

test('a contact message can be viewed', function () {
    $message = ContactMessage::factory()->create([
        'message' => 'Need help with office coffee.',
    ]);

    $this->actingAs($this->admin)
        ->get(route('admin.contact-messages.show', $message))
        ->assertOk()
        ->assertInertia(
            fn(Assert $page) => $page
                ->component('admin/contact-messages/show')
                ->where('message.id', $message->id)
                ->where('message.message', 'Need help with office coffee.')
        );
});

test('a contact message status can be updated', function () {
    $message = ContactMessage::factory()->create([
        'status' => ContactMessage::STATUS_NEW,
    ]);

    $this->actingAs($this->admin)
        ->put(route('admin.contact-messages.update', $message), [
            'status' => ContactMessage::STATUS_REVIEWED,
        ])
        ->assertRedirect();

    expect($message->fresh()->status)->toBe(ContactMessage::STATUS_REVIEWED);
});

test('a contact message can be deleted', function () {
    $message = ContactMessage::factory()->create();

    $this->actingAs($this->admin)
        ->delete(route('admin.contact-messages.destroy', $message))
        ->assertRedirect(route('admin.contact-messages.index'));

    expect(ContactMessage::query()->find($message->id))->toBeNull();
});

test('contact messages can be filtered by status', function () {
    ContactMessage::factory()->create(['name' => 'New Person']);
    ContactMessage::factory()->reviewed()->create(['name' => 'Reviewed Person']);

    $this->actingAs($this->admin)
        ->get(route('admin.contact-messages.index', ['status' => 'reviewed']))
        ->assertOk()
        ->assertInertia(
            fn(Assert $page) => $page
                ->has('messages.data', 1)
                ->where('messages.data.0.name', 'Reviewed Person')
                ->where('filters.status', 'reviewed')
        );
});
