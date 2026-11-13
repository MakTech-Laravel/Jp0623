<?php

use App\Models\LocationType;
use App\Models\VendingRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    LocationType::factory()->create([
        'name' => 'Business / office',
        'is_active' => true,
    ]);
});

/**
 * @return array<string, mixed>
 */
function validVendingRequestPayload(array $overrides = []): array
{
    return [
        'location_type' => 'Business / office',
        'business_name' => 'Riverside Logistics',
        'address_or_zip' => '100 Main St, Houston, TX 77002',
        'address' => '100 Main St',
        'city' => 'Houston',
        'state' => 'TX',
        'postal_code' => '77002',
        'latitude' => 29.7604,
        'longitude' => -95.3698,
        'employees_on_site' => 60,
        'daily_visitors' => 150,
        'placement' => 'Inside',
        'space_notes' => 'Break room',
        'food_on_site' => 'No',
        'comparing_quotes' => 'No',
        'machine_interests' => ['Snack', 'Drinks'],
        'machine_count' => '2',
        'existing_vending' => 'No',
        'switch_reason' => null,
        'contact_name' => 'Jane Doe',
        'job_title' => 'Office manager',
        'email' => 'jane@example.com',
        'phone' => '(555) 555-5555',
        'terms_accepted' => true,
        ...$overrides,
    ];
}

test('a guest can submit a vending request', function () {
    $this->from('/')
        ->post(route('vending-requests.store'), validVendingRequestPayload())
        ->assertRedirect('/#request-sec');

    $request = VendingRequest::query()->sole();

    expect($request->business_name)->toBe('Riverside Logistics')
        ->and($request->email)->toBe('jane@example.com')
        ->and($request->city)->toBe('Houston')
        ->and($request->state)->toBe('TX')
        ->and($request->postal_code)->toBe('77002')
        ->and($request->address)->toBe('100 Main St')
        ->and((float) $request->latitude)->toBe(29.7604)
        ->and((float) $request->longitude)->toBe(-95.3698)
        ->and($request->address_or_zip)->toBe('100 Main St, Houston, TX 77002')
        ->and($request->machine_interests)->toBe(['Snack', 'Drinks'])
        ->and($request->status)->toBe(VendingRequest::STATUS_NEW)
        ->and($request->price)->toBeNull()
        ->and($request->terms_accepted)->toBeTrue();
});

test('vending request validation requires core fields and location', function () {
    $this->from('/')
        ->post(route('vending-requests.store'), [
            'location_type' => '',
            'business_name' => '',
            'address_or_zip' => '',
            'city' => '',
            'state' => '',
            'latitude' => null,
            'longitude' => null,
            'placement' => '',
            'food_on_site' => '',
            'comparing_quotes' => '',
            'machine_interests' => [],
            'machine_count' => '',
            'existing_vending' => '',
            'contact_name' => '',
            'email' => 'not-an-email',
            'phone' => '',
            'terms_accepted' => false,
        ])
        ->assertSessionHasErrors([
            'location_type',
            'business_name',
            'city',
            'state',
            'latitude',
            'longitude',
            'placement',
            'food_on_site',
            'comparing_quotes',
            'machine_interests',
            'machine_count',
            'existing_vending',
            'contact_name',
            'email',
            'phone',
            'terms_accepted',
        ]);

    expect(VendingRequest::query()->count())->toBe(0);
});

test('vending request rejects location without coordinates', function () {
    $this->from('/')
        ->post(route('vending-requests.store'), validVendingRequestPayload([
            'latitude' => null,
            'longitude' => null,
        ]))
        ->assertSessionHasErrors(['latitude', 'longitude']);
});

test('vending request rejects inactive location types', function () {
    LocationType::factory()->inactive()->create(['name' => 'Closed site']);

    $this->from('/')
        ->post(route('vending-requests.store'), validVendingRequestPayload([
            'location_type' => 'Closed site',
        ]))
        ->assertSessionHasErrors(['location_type']);
});

test('switch reason is required when changing providers', function () {
    $this->from('/')
        ->post(route('vending-requests.store'), validVendingRequestPayload([
            'existing_vending' => 'Yes, want to switch',
            'switch_reason' => '',
        ]))
        ->assertSessionHasErrors(['switch_reason']);
});

test('switch reason is cleared when existing vending is no', function () {
    $this->from('/')
        ->post(route('vending-requests.store'), validVendingRequestPayload([
            'existing_vending' => 'No',
            'switch_reason' => 'Should be ignored',
        ]))
        ->assertRedirect('/#request-sec');

    expect(VendingRequest::query()->sole()->switch_reason)->toBeNull();
});

test('phone rejects letters and too few digits', function () {
    $this->from('/')
        ->post(route('vending-requests.store'), validVendingRequestPayload([
            'phone' => 'not-a-phone',
        ]))
        ->assertSessionHasErrors(['phone']);

    $this->from('/')
        ->post(route('vending-requests.store'), validVendingRequestPayload([
            'phone' => '123',
        ]))
        ->assertSessionHasErrors(['phone']);
});
