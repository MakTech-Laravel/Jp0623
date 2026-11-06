<?php

use App\Enums\RoleEnum;
use App\Models\User;
use App\Models\VendingRequest;
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

test('guests cannot access vending requests', function () {
    $this->get(route('admin.vending-requests.index'))
        ->assertRedirect(route('login'));
});

test('users without permission are forbidden from vending requests', function () {
    $plain = User::factory()->create();

    $this->actingAs($plain)
        ->get(route('admin.vending-requests.index'))
        ->assertForbidden();
});

test('super admin can view the vending requests list', function () {
    VendingRequest::factory()->create([
        'business_name' => 'Riverside Logistics',
    ]);

    $this->actingAs($this->admin)
        ->get(route('admin.vending-requests.index'))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('admin/vending-requests/index')
                ->has('requests.data', 1)
                ->has('filters')
        );
});

test('a vending request can be viewed', function () {
    $request = VendingRequest::factory()->create();

    $this->actingAs($this->admin)
        ->get(route('admin.vending-requests.show', $request))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('admin/vending-requests/show')
                ->where('vendingRequest.id', $request->id)
        );
});

test('a vending request status can be updated', function () {
    $request = VendingRequest::factory()->create([
        'status' => VendingRequest::STATUS_NEW,
    ]);

    $this->actingAs($this->admin)
        ->put(route('admin.vending-requests.update', $request), [
            'status' => VendingRequest::STATUS_REVIEWED,
        ])
        ->assertRedirect();

    expect($request->fresh()->status)->toBe(VendingRequest::STATUS_REVIEWED);
});

test('a vending request can be deleted', function () {
    $request = VendingRequest::factory()->create();

    $this->actingAs($this->admin)
        ->delete(route('admin.vending-requests.destroy', $request))
        ->assertRedirect(route('admin.vending-requests.index'));

    expect(VendingRequest::query()->whereKey($request->id)->exists())->toBeFalse();
});
