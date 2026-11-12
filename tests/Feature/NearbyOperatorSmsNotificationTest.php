<?php

use App\Enums\OperatorSmsStatus;
use App\Jobs\NotifyNearbyOperatorsOfLead;
use App\Models\CoffeeServiceRequest;
use App\Models\LocationType;
use App\Models\OperatorProfile;
use App\Models\SmsTemplate;
use App\Models\VendingRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

beforeEach(function () {
    LocationType::factory()->create([
        'name' => 'Business / office',
        'is_active' => true,
    ]);

    config([
        'services.twilio.enabled' => true,
        'services.twilio.sid' => 'ACtestsid',
        'services.twilio.token' => 'test-token',
        'services.twilio.from' => '+15005550006',
        'services.twilio.lead_radius_miles' => 100,
        'services.twilio.sandbox_message_prefix' => '',
        'services.twilio.messaging_service_sid' => null,
    ]);
});

/**
 * @return array<string, mixed>
 */
function nearbySmsVendingPayload(array $overrides = []): array
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

/**
 * @return array<string, mixed>
 */
function nearbySmsCoffeePayload(array $overrides = []): array
{
    return [
        'business_name' => 'Harbor Offices',
        'zip_code' => '77002',
        'address' => '100 Main St',
        'city' => 'Houston',
        'state' => 'TX',
        'postal_code' => '77002',
        'latitude' => 29.7604,
        'longitude' => -95.3698,
        'contact_name' => 'Jane Doe',
        'phone' => '(555) 555-5555',
        'email' => 'jane@example.com',
        'notes' => 'Prefer morning delivery.',
        'terms_accepted' => true,
        ...$overrides,
    ];
}

test('vending request submission dispatches nearby operator sms job', function () {
    Queue::fake();

    $this->from('/')
        ->post(route('vending-requests.store'), nearbySmsVendingPayload())
        ->assertRedirect('/#request-sec');

    $request = VendingRequest::query()->sole();

    Queue::assertPushed(NotifyNearbyOperatorsOfLead::class, function (NotifyNearbyOperatorsOfLead $job) use ($request): bool {
        return $job->leadType === 'vending' && $job->leadId === $request->id;
    });
});

test('coffee request submission dispatches nearby operator sms job', function () {
    Queue::fake();

    $this->from('/office-coffee')
        ->post(route('coffee-service-requests.store'), nearbySmsCoffeePayload())
        ->assertRedirect('/office-coffee#coffee-form');

    $request = CoffeeServiceRequest::query()->sole();

    Queue::assertPushed(NotifyNearbyOperatorsOfLead::class, function (NotifyNearbyOperatorsOfLead $job) use ($request): bool {
        return $job->leadType === 'coffee' && $job->leadId === $request->id;
    });
});

test('nearby operators within 100 miles receive twilio sms for a vending lead', function () {
    Http::fake([
        'api.twilio.com/*' => Http::response(['sid' => 'SMxxxxxxxx', 'status' => 'queued'], 201),
    ]);

    SmsTemplate::factory()->autoVending()->create([
        'body' => 'TEMPLATE VENDING {{business_name}} {{phone}}',
        'is_active' => true,
    ]);

    OperatorProfile::factory()->create([
        'phone' => '(713) 555-0100',
        'latitude' => 29.7604,
        'longitude' => -95.3698,
    ]);

    OperatorProfile::factory()->create([
        'phone' => '(212) 555-0199',
        'latitude' => 40.7128,
        'longitude' => -74.0060,
    ]);

    $this->from('/')
        ->post(route('vending-requests.store'), nearbySmsVendingPayload())
        ->assertRedirect('/#request-sec');

    Http::assertSentCount(1);

    Http::assertSent(function ($request): bool {
        return $request->url() === 'https://api.twilio.com/2010-04-01/Accounts/ACtestsid/Messages.json'
            && $request['To'] === '+17135550100'
            && $request['From'] === '+15005550006'
            && str_contains((string) $request['Body'], 'TEMPLATE VENDING Riverside Logistics')
            && str_contains((string) $request['Body'], '(555) 555-5555');
    });

    $lead = VendingRequest::query()->sole();

    expect($lead->operator_sms_status)->toBe(OperatorSmsStatus::DONE->value)
        ->and($lead->operator_sms_sent_count)->toBe(1)
        ->and($lead->operator_sms_failed_count)->toBe(0)
        ->and($lead->operator_sms_provider_status)->toBe('queued');
});

test('nearby operators within 100 miles receive twilio sms for a coffee lead', function () {
    Http::fake([
        'api.twilio.com/*' => Http::response(['sid' => 'SMyyyyyyyy', 'status' => 'queued'], 201),
    ]);

    SmsTemplate::factory()->autoCoffee()->create([
        'body' => 'TEMPLATE COFFEE {{business_name}}',
        'is_active' => true,
    ]);

    OperatorProfile::factory()->create([
        'phone' => '7135550101',
        'latitude' => 29.7490,
        'longitude' => -95.3580,
    ]);

    $this->from('/office-coffee')
        ->post(route('coffee-service-requests.store'), nearbySmsCoffeePayload())
        ->assertRedirect('/office-coffee#coffee-form');

    Http::assertSentCount(1);

    Http::assertSent(function ($request): bool {
        return $request['To'] === '+17135550101'
            && str_contains((string) $request['Body'], 'TEMPLATE COFFEE Harbor Offices');
    });

    $lead = CoffeeServiceRequest::query()->sole();

    expect($lead->operator_sms_status)->toBe(OperatorSmsStatus::DONE->value)
        ->and($lead->operator_sms_sent_count)->toBe(1)
        ->and($lead->operator_sms_provider_status)->toBe('queued');
});

test('sms is skipped when twilio is disabled', function () {
    config(['services.twilio.enabled' => false]);

    Http::fake();

    OperatorProfile::factory()->create([
        'phone' => '(713) 555-0100',
        'latitude' => 29.7604,
        'longitude' => -95.3698,
    ]);

    $this->from('/')
        ->post(route('vending-requests.store'), nearbySmsVendingPayload())
        ->assertRedirect('/#request-sec');

    Http::assertNothingSent();

    expect(VendingRequest::query()->sole()->operator_sms_status)
        ->toBe(OperatorSmsStatus::SKIPPED->value);
});

test('operator sms status is failed when twilio rejects the message', function () {
    Http::fake([
        'api.twilio.com/*' => Http::response([
            'code' => 21608,
            'message' => 'Unverified number',
            'status' => 400,
        ], 400),
    ]);

    OperatorProfile::factory()->create([
        'phone' => '(713) 555-0100',
        'latitude' => 29.7604,
        'longitude' => -95.3698,
    ]);

    $this->from('/office-coffee')
        ->post(route('coffee-service-requests.store'), nearbySmsCoffeePayload())
        ->assertRedirect('/office-coffee#coffee-form');

    $lead = CoffeeServiceRequest::query()->sole();

    expect($lead->operator_sms_status)->toBe(OperatorSmsStatus::FAILED->value)
        ->and($lead->operator_sms_failed_count)->toBe(1)
        ->and($lead->operator_sms_sent_count)->toBe(0);
});
