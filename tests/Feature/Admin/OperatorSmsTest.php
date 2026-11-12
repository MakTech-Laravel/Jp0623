<?php

use App\Enums\RoleEnum;
use App\Jobs\SendAdminOperatorSms;
use App\Models\OperatorProfile;
use App\Models\User;
use App\Models\VendingRequest;
use App\Services\TwilioSmsService;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed([PermissionSeeder::class, RoleSeeder::class]);

    $this->admin = User::factory()->create();
    $this->admin->assignRole(RoleEnum::SUPER_ADMIN->value);

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

test('guests cannot access operator sms', function () {
    $this->get(route('admin.operator-sms.index'))
        ->assertRedirect(route('login'));
});

test('users without permission are forbidden from operator sms', function () {
    $plain = User::factory()->create();

    $this->actingAs($plain)
        ->get(route('admin.operator-sms.index'))
        ->assertForbidden();
});

test('admin can view operators filtered by mile range from a lead', function () {
    $lead = VendingRequest::factory()->create([
        'latitude' => 29.7604,
        'longitude' => -95.3698,
        'business_name' => 'Riverside Logistics',
    ]);

    $near = OperatorProfile::factory()->create([
        'phone' => '(713) 555-0100',
        'latitude' => 29.7604,
        'longitude' => -95.3698,
        'first_name' => 'Near',
        'last_name' => 'Operator',
        'company_name' => 'Near Co',
    ]);

    OperatorProfile::factory()->create([
        'phone' => '(212) 555-0199',
        'latitude' => 40.7128,
        'longitude' => -74.0060,
        'first_name' => 'Far',
        'last_name' => 'Operator',
    ]);

    $this->actingAs($this->admin)
        ->get(route('admin.operator-sms.index', [
            'latitude' => $lead->latitude,
            'longitude' => $lead->longitude,
            'range' => '0-100',
            'reference_key' => 'vending:' . $lead->id,
        ]))
        ->assertOk()
        ->assertInertia(
            fn(Assert $page) => $page
                ->component('admin/operator-sms/index')
                ->has('operators', 1)
                ->where('operators.0.id', $near->id)
                ->where('filters.range', '0-100')
        );
});

test('admin can search operators by name or email', function () {
    $match = OperatorProfile::factory()->create([
        'phone' => '(713) 555-0100',
        'latitude' => 29.7604,
        'longitude' => -95.3698,
        'first_name' => 'Harbor',
        'last_name' => 'Vending',
        'company_name' => 'Harbor Co',
    ]);
    $match->user->update([
        'email' => 'harbor@example.com',
        'name' => 'Harbor Vending',
    ]);

    OperatorProfile::factory()->create([
        'phone' => '(713) 555-0199',
        'latitude' => 29.7610,
        'longitude' => -95.3700,
        'first_name' => 'Other',
        'last_name' => 'Person',
    ]);

    $this->actingAs($this->admin)
        ->get(route('admin.operator-sms.index', ['search' => 'harbor@example.com']))
        ->assertOk()
        ->assertInertia(
            fn(Assert $page) => $page
                ->component('admin/operator-sms/index')
                ->has('operators', 1)
                ->where('operators.0.id', $match->id)
        );
});

test('admin can queue sms to selected operators', function () {
    Queue::fake();

    $first = OperatorProfile::factory()->create([
        'phone' => '(713) 555-0100',
        'latitude' => 29.7604,
        'longitude' => -95.3698,
    ]);
    $second = OperatorProfile::factory()->create([
        'phone' => '(713) 555-0101',
        'latitude' => 29.7610,
        'longitude' => -95.3700,
    ]);

    $this->actingAs($this->admin)
        ->post(route('admin.operator-sms.send'), [
            'operator_ids' => [$first->id, $second->id],
            'message' => 'Manual admin outreach test',
        ])
        ->assertRedirect();

    Queue::assertPushed(SendAdminOperatorSms::class, function (SendAdminOperatorSms $job) use ($first, $second): bool {
        return $job->message === 'Manual admin outreach test'
            && $job->operatorProfileIds === [$first->id, $second->id];
    });
});

test('admin sms job sends twilio messages to selected operators', function () {
    Http::fake([
        'api.twilio.com/*' => Http::response(['sid' => 'SMadmin', 'status' => 'queued'], 201),
    ]);

    $operator = OperatorProfile::factory()->create([
        'phone' => '7135550100',
        'latitude' => 29.7604,
        'longitude' => -95.3698,
    ]);

    (new SendAdminOperatorSms([$operator->id], 'Hello operators'))->handle(
        app(TwilioSmsService::class),
    );

    Http::assertSentCount(1);
    Http::assertSent(function ($request): bool {
        return $request['To'] === '+17135550100'
            && $request['Body'] === 'Hello operators';
    });
});

test('send validation requires operators and message', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.operator-sms.send'), [
            'operator_ids' => [],
            'message' => '',
        ])
        ->assertSessionHasErrors(['operator_ids', 'message']);
});
