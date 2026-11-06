<?php

use App\Models\VendingRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * @return array<string, mixed>
 */
function validVendingRequestPayload(array $overrides = []): array
{
    return [
        'location_type' => 'Business / office',
        'business_name' => 'Riverside Logistics',
        'address_or_zip' => '77002',
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
        'captcha_confirmed' => true,
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
        ->and($request->machine_interests)->toBe(['Snack', 'Drinks'])
        ->and($request->status)->toBe(VendingRequest::STATUS_NEW)
        ->and($request->terms_accepted)->toBeTrue();
});

test('vending request validation requires core fields', function () {
    $this->from('/')
        ->post(route('vending-requests.store'), [
            'location_type' => '',
            'business_name' => '',
            'address_or_zip' => '',
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
            'captcha_confirmed' => false,
        ])
        ->assertSessionHasErrors([
            'location_type',
            'business_name',
            'address_or_zip',
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
            'captcha_confirmed',
        ]);

    expect(VendingRequest::query()->count())->toBe(0);
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

test('employees and daily visitors must be integers when provided', function () {
    $this->from('/')
        ->post(route('vending-requests.store'), validVendingRequestPayload([
            'employees_on_site' => 'sixty',
            'daily_visitors' => '150 people',
        ]))
        ->assertSessionHasErrors(['employees_on_site', 'daily_visitors']);
});

test('empty employees and daily visitors are stored as null', function () {
    $this->from('/')
        ->post(route('vending-requests.store'), validVendingRequestPayload([
            'employees_on_site' => '',
            'daily_visitors' => '',
        ]))
        ->assertRedirect('/#request-sec');

    $request = VendingRequest::query()->sole();

    expect($request->employees_on_site)->toBeNull()
        ->and($request->daily_visitors)->toBeNull();
});
