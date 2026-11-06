<?php

use App\Models\CoffeeServiceRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * @return array<string, mixed>
 */
function validCoffeeServiceRequestPayload(array $overrides = []): array
{
    return [
        'business_name' => 'Harbor Offices',
        'zip_code' => '77002',
        'contact_name' => 'Jane Doe',
        'phone' => '(555) 555-5555',
        'email' => 'jane@example.com',
        'notes' => 'Prefer morning delivery.',
        'terms_accepted' => true,
        ...$overrides,
    ];
}

test('a guest can submit a coffee service request', function () {
    $this->from('/office-coffee')
        ->post(route('coffee-service-requests.store'), validCoffeeServiceRequestPayload())
        ->assertRedirect('/office-coffee#coffee-form');

    $request = CoffeeServiceRequest::query()->sole();

    expect($request->business_name)->toBe('Harbor Offices')
        ->and($request->zip_code)->toBe('77002')
        ->and($request->email)->toBe('jane@example.com')
        ->and($request->notes)->toBe('Prefer morning delivery.')
        ->and($request->status)->toBe(CoffeeServiceRequest::STATUS_NEW)
        ->and($request->terms_accepted)->toBeTrue();
});

test('coffee service request validation requires core fields', function () {
    $this->from('/office-coffee')
        ->post(route('coffee-service-requests.store'), [
            'business_name' => '',
            'zip_code' => '',
            'contact_name' => '',
            'phone' => '',
            'email' => 'not-an-email',
            'notes' => '',
            'terms_accepted' => false,
        ])
        ->assertSessionHasErrors([
            'business_name',
            'zip_code',
            'contact_name',
            'phone',
            'email',
            'terms_accepted',
        ]);

    expect(CoffeeServiceRequest::query()->count())->toBe(0);
});

test('coffee service phone rejects letters and too few digits', function () {
    $this->from('/office-coffee')
        ->post(route('coffee-service-requests.store'), validCoffeeServiceRequestPayload([
            'phone' => 'not-a-phone',
        ]))
        ->assertSessionHasErrors(['phone']);

    $this->from('/office-coffee')
        ->post(route('coffee-service-requests.store'), validCoffeeServiceRequestPayload([
            'phone' => '123',
        ]))
        ->assertSessionHasErrors(['phone']);
});

test('empty notes are stored as null', function () {
    $this->from('/office-coffee')
        ->post(route('coffee-service-requests.store'), validCoffeeServiceRequestPayload([
            'notes' => '   ',
        ]))
        ->assertRedirect('/office-coffee#coffee-form');

    expect(CoffeeServiceRequest::query()->sole()->notes)->toBeNull();
});
