<?php

use App\Enums\RoleEnum;
use App\Jobs\NotifyNearbyOperatorsOfLead;
use App\Models\LeadPurchase;
use App\Models\LocationType;
use App\Models\User;
use App\Models\VendingRequest;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
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
                ->where('requests.data.0.is_sold', false)
                ->where('requests.data.0.is_held', false)
                ->has('filters.sale')
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
                ->where('vendingRequest.is_sold', false)
                ->where('vendingRequest.is_held', false)
                ->where('leadBuyer', null)
        );
});

test('sold and pending vending leads expose sale flags and buyer', function () {
    $operator = makeOperator();
    $soldLead = VendingRequest::factory()->create(['business_name' => 'Sold Site']);
    $pendingLead = VendingRequest::factory()->create(['business_name' => 'Pending Site']);
    $availableLead = VendingRequest::factory()->create(['business_name' => 'Open Site']);

    LeadPurchase::factory()->create([
        'user_id' => $operator->id,
        'leadable_type' => 'vending',
        'leadable_id' => $soldLead->id,
    ]);

    LeadPurchase::factory()->pending()->create([
        'user_id' => $operator->id,
        'leadable_type' => 'vending',
        'leadable_id' => $pendingLead->id,
    ]);

    $this->actingAs($this->admin)
        ->get(route('admin.vending-requests.index', ['sale' => 'sold']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('requests.data', 1)
            ->where('requests.data.0.id', $soldLead->id)
            ->where('requests.data.0.is_sold', true)
            ->where('requests.data.0.is_held', true)
            ->where('filters.sale', 'sold')
        );

    $this->actingAs($this->admin)
        ->get(route('admin.vending-requests.index', ['sale' => 'processing']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('requests.data', 1)
            ->where('requests.data.0.id', $pendingLead->id)
            ->where('requests.data.0.is_sold', false)
            ->where('requests.data.0.is_held', true)
        );

    $this->actingAs($this->admin)
        ->get(route('admin.vending-requests.index', ['sale' => 'available']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('requests.data', 1)
            ->where('requests.data.0.id', $availableLead->id)
            ->where('requests.data.0.is_sold', false)
            ->where('requests.data.0.is_held', false)
        );

    $this->actingAs($this->admin)
        ->get(route('admin.vending-requests.show', $soldLead))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('vendingRequest.is_sold', true)
            ->where('leadBuyer.email', $operator->email)
            ->where('leadBuyer.purchase_status', LeadPurchase::STATUS_PAID)
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

test('admin can create a vending request with a custom price', function () {
    Queue::fake();
    LocationType::factory()->create(['name' => 'Business / office', 'is_active' => true]);

    $this->actingAs($this->admin)
        ->post(route('admin.vending-requests.store'), [
            'location_type' => 'Business / office',
            'business_name' => 'Manual Lead Co',
            'address_or_zip' => '100 Main St, Houston, TX 77002',
            'address' => '100 Main St',
            'city' => 'Houston',
            'state' => 'TX',
            'postal_code' => '77002',
            'latitude' => 29.7604,
            'longitude' => -95.3698,
            'employees_on_site' => 40,
            'daily_visitors' => 100,
            'placement' => 'Inside',
            'space_notes' => 'Break room',
            'food_on_site' => 'No',
            'comparing_quotes' => 'Yes',
            'machine_interests' => ['Snack', 'Drinks'],
            'machine_count' => '2',
            'existing_vending' => 'No',
            'contact_name' => 'Alex Admin',
            'job_title' => 'Facilities',
            'email' => 'alex@example.com',
            'phone' => '555-0100',
            'price' => 225,
        ])
        ->assertRedirect();

    $request = VendingRequest::query()->where('business_name', 'Manual Lead Co')->sole();

    expect($request->price)->toBe(225)
        ->and($request->city)->toBe('Houston')
        ->and($request->contact_name)->toBe('Alex Admin')
        ->and($request->terms_accepted)->toBeTrue();

    Queue::assertPushed(NotifyNearbyOperatorsOfLead::class);
});

test('admin can update lead price until the lead is sold', function () {
    $request = VendingRequest::factory()->create(['price' => 150]);

    $this->actingAs($this->admin)
        ->put(route('admin.vending-requests.update', $request), [
            'price' => 199,
        ])
        ->assertRedirect();

    expect($request->fresh()->price)->toBe(199);

    LeadPurchase::factory()->create([
        'user_id' => makeOperator()->id,
        'leadable_type' => 'vending',
        'leadable_id' => $request->id,
    ]);

    $this->actingAs($this->admin)
        ->put(route('admin.vending-requests.update', $request), [
            'price' => 250,
        ])
        ->assertSessionHasErrors('price');

    expect($request->fresh()->price)->toBe(199);
});
