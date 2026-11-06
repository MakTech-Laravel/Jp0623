<?php

use App\Enums\RoleEnum;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed([PermissionSeeder::class, RoleSeeder::class]);

    $this->admin = User::factory()->create();
    $this->admin->assignRole(RoleEnum::ADMIN->value);

    $this->superAdmin = User::factory()->create();
    $this->superAdmin->assignRole(RoleEnum::SUPER_ADMIN->value);
});

test('an admin can create admins via admin management', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.admin-management.store'), [
            'name' => 'New Admin',
            'email' => 'new-admin@example.com',
            'password' => 'password123',
        ])
        ->assertRedirect(route('admin.admin-management.index'));

    $created = User::where('email', 'new-admin@example.com')->sole();

    expect($created->hasRole(RoleEnum::ADMIN->value))->toBeTrue()
        ->and($created->isSuperAdmin())->toBeFalse();
});

test('client-supplied roles are ignored when creating through user management', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.user-management.store'), [
            'first_name' => 'Sneaky',
            'last_name' => 'User',
            'email' => 'sneaky@example.com',
            'password' => 'password123',
            'company_name' => 'Sneaky Co',
            'owns_machines' => false,
            'machine_types' => ['Snack'],
            'machines_needed' => '1',
            'primary_city' => 'Dallas, TX',
            'roles' => [RoleEnum::SUPER_ADMIN->value],
        ])
        ->assertRedirect(route('admin.user-management.index'));

    $created = User::where('email', 'sneaky@example.com')->sole();

    expect($created->isSuperAdmin())->toBeFalse()
        ->and($created->hasRole(RoleEnum::USER->value))->toBeTrue()
        ->and($created->operatorProfile?->company_name)->toBe('Sneaky Co');
});

test('a non super-admin cannot open a super-admin via user management', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.user-management.edit', $this->superAdmin))
        ->assertNotFound();
});

test('a non super-admin cannot update a super-admin via user management', function () {
    $this->actingAs($this->admin)
        ->put(route('admin.user-management.update', $this->superAdmin), [
            'name' => 'Hijacked',
            'email' => $this->superAdmin->email,
        ])
        ->assertNotFound();

    expect($this->superAdmin->fresh()->name)->not->toBe('Hijacked');
});

test('a non super-admin cannot delete a super-admin via user management', function () {
    $this->actingAs($this->admin)
        ->delete(route('admin.user-management.destroy', $this->superAdmin))
        ->assertNotFound();

    expect(User::find($this->superAdmin->id))->not->toBeNull();
});

test('an admin can edit another admin via admin management', function () {
    $otherAdmin = User::factory()->create();
    $otherAdmin->assignRole(RoleEnum::ADMIN->value);

    $this->actingAs($this->admin)
        ->get(route('admin.admin-management.edit', $otherAdmin))
        ->assertOk();
});

test('admin management cannot open a user-role account', function () {
    $user = User::factory()->create();
    $user->assignRole(RoleEnum::USER->value);

    $this->actingAs($this->superAdmin)
        ->get(route('admin.admin-management.edit', $user))
        ->assertNotFound();
});
