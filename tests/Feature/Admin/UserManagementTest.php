<?php

use App\Enums\RoleEnum;
use App\Models\OperatorProfile;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed([PermissionSeeder::class, RoleSeeder::class]);

    $this->superAdmin = User::factory()->create();
    $this->superAdmin->assignRole(RoleEnum::SUPER_ADMIN->value);
});

/**
 * @return array<string, mixed>
 */
function validUserManagementPayload(array $overrides = []): array
{
    return [
        'first_name' => 'Jane',
        'last_name' => 'Doe',
        'email' => 'jane@example.com',
        'password' => 'password123',
        'company_name' => 'Harbor Vending',
        'owns_machines' => true,
        'machine_types' => ['Snack', 'Combo'],
        'machines_needed' => '2',
        'primary_city' => 'Houston, TX',
        ...$overrides,
    ];
}

test('guests cannot access user management', function () {
    $this->get(route('admin.user-management.index'))->assertRedirect(route('login'));
});

test('users without permission are forbidden', function () {
    $plain = User::factory()->create();

    $this->actingAs($plain)->get(route('admin.user-management.index'))->assertForbidden();
});

test('super admin can view the user management list', function () {
    User::factory()->create()->assignRole(RoleEnum::USER->value);

    $this->actingAs($this->superAdmin)
        ->get(route('admin.user-management.index'))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('admin/users/index')
                ->where('scope', 'user')
                ->has('users.data', 1)
        );
});

test('user management create always assigns the user role and profile', function () {
    $this->actingAs($this->superAdmin)
        ->post(route('admin.user-management.store'), validUserManagementPayload([
            'roles' => [RoleEnum::ADMIN->value],
        ]))
        ->assertRedirect(route('admin.user-management.index'));

    $user = User::where('email', 'jane@example.com')->sole();

    expect($user->name)->toBe('Jane Doe')
        ->and($user->hasRole(RoleEnum::USER->value))->toBeTrue()
        ->and($user->hasRole(RoleEnum::ADMIN->value))->toBeFalse()
        ->and(Hash::check('password123', $user->password))->toBeTrue()
        ->and($user->operatorProfile)->not->toBeNull()
        ->and($user->operatorProfile->company_name)->toBe('Harbor Vending')
        ->and($user->operatorProfile->machine_types)->toBe(['Snack', 'Combo']);
});

test('creating a user validates required fields', function () {
    $this->actingAs($this->superAdmin)
        ->post(route('admin.user-management.store'), [
            'first_name' => '',
            'last_name' => '',
            'email' => 'not-an-email',
            'password' => 'short',
        ])
        ->assertSessionHasErrors(['first_name', 'last_name', 'email', 'password', 'company_name', 'owns_machines', 'machine_types', 'machines_needed', 'primary_city']);

    expect(User::where('email', 'not-an-email')->exists())->toBeFalse();
});

test('the email must be unique', function () {
    User::factory()->create(['email' => 'taken@example.com']);

    $this->actingAs($this->superAdmin)
        ->post(route('admin.user-management.store'), validUserManagementPayload([
            'email' => 'taken@example.com',
        ]))
        ->assertSessionHasErrors('email');
});

test('a user can be updated while keeping the user role and syncing profile', function () {
    $user = User::factory()->create();
    $user->assignRole(RoleEnum::USER->value);
    OperatorProfile::factory()->create([
        'user_id' => $user->id,
        'first_name' => 'Old',
        'last_name' => 'Name',
    ]);

    $this->actingAs($this->superAdmin)
        ->put(route('admin.user-management.update', $user), validUserManagementPayload([
            'first_name' => 'Renamed',
            'last_name' => 'Person',
            'email' => $user->email,
            'password' => '',
            'company_name' => 'New Co',
            'roles' => [RoleEnum::ADMIN->value],
        ]))
        ->assertRedirect(route('admin.user-management.index'));

    $user->refresh();

    expect($user->name)->toBe('Renamed Person')
        ->and($user->hasRole(RoleEnum::USER->value))->toBeTrue()
        ->and($user->hasRole(RoleEnum::ADMIN->value))->toBeFalse()
        ->and($user->operatorProfile->company_name)->toBe('New Co')
        ->and($user->operatorProfile->first_name)->toBe('Renamed');
});

test('updating without a password keeps the current one', function () {
    $user = User::factory()->create(['password' => 'secret-pass']);
    $user->assignRole(RoleEnum::USER->value);
    OperatorProfile::factory()->create(['user_id' => $user->id]);

    $this->actingAs($this->superAdmin)
        ->put(route('admin.user-management.update', $user), validUserManagementPayload([
            'first_name' => 'Keep',
            'last_name' => 'Pass',
            'email' => $user->email,
            'password' => '',
        ]))
        ->assertRedirect();

    expect(Hash::check('secret-pass', $user->fresh()->password))->toBeTrue();
});

test('user show includes operator profile registration fields', function () {
    $user = User::factory()->create(['name' => 'Jane Doe']);
    $user->assignRole(RoleEnum::USER->value);
    OperatorProfile::factory()->create([
        'user_id' => $user->id,
        'first_name' => 'Jane',
        'last_name' => 'Doe',
        'company_name' => 'Harbor Vending',
        'primary_city' => 'Houston, TX',
        'machine_types' => ['Coffee', 'Snack'],
    ]);

    $this->actingAs($this->superAdmin)
        ->get(route('admin.user-management.show', $user))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('admin/users/show')
                ->where('scope', 'user')
                ->where('operatorProfile.company_name', 'Harbor Vending')
                ->where('operatorProfile.primary_city', 'Houston, TX')
                ->where('operatorProfile.machine_types', ['Coffee', 'Snack'])
        );
});

test('a user can be deleted', function () {
    $user = User::factory()->create();
    $user->assignRole(RoleEnum::USER->value);

    $this->actingAs($this->superAdmin)
        ->delete(route('admin.user-management.destroy', $user))
        ->assertRedirect();

    expect(User::find($user->id))->toBeNull();
});

test('user management cannot open an admin account', function () {
    $admin = User::factory()->create();
    $admin->assignRole(RoleEnum::ADMIN->value);

    $this->actingAs($this->superAdmin)
        ->get(route('admin.user-management.edit', $admin))
        ->assertNotFound();
});

test('admin management create always assigns the admin role', function () {
    $this->actingAs($this->superAdmin)
        ->post(route('admin.admin-management.store'), [
            'name' => 'Site Admin',
            'email' => 'site-admin@example.com',
            'password' => 'password123',
            'roles' => [RoleEnum::USER->value],
        ])
        ->assertRedirect(route('admin.admin-management.index'));

    $admin = User::where('email', 'site-admin@example.com')->sole();

    expect($admin->hasRole(RoleEnum::ADMIN->value))->toBeTrue()
        ->and($admin->hasRole(RoleEnum::USER->value))->toBeFalse();
});

test('an admin can access admin management', function () {
    $admin = User::factory()->create();
    $admin->assignRole(RoleEnum::ADMIN->value);

    $this->actingAs($admin)
        ->get(route('admin.admin-management.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/users/index')
            ->where('scope', 'admin'));
});

test('an admin can access user management via gate bypass', function () {
    $admin = User::factory()->create();
    $admin->assignRole(RoleEnum::ADMIN->value);

    $this->actingAs($admin)
        ->get(route('admin.user-management.index'))
        ->assertOk();
});

test('an admin can open roles and permissions routes via gate bypass', function () {
    $admin = User::factory()->create();
    $admin->assignRole(RoleEnum::ADMIN->value);

    $this->actingAs($admin)
        ->get(route('admin.roles.index'))
        ->assertOk();

    $this->actingAs($admin)
        ->get(route('admin.permissions.index'))
        ->assertOk();
});

test('an admin can open coffee service requests via gate bypass', function () {
    $admin = User::factory()->create();
    $admin->assignRole(RoleEnum::ADMIN->value);

    $this->actingAs($admin)
        ->get(route('admin.coffee-service-requests.index'))
        ->assertOk();
});
