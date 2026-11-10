<?php

use App\Enums\RoleEnum;
use App\Mail\RegisterOtpMail;
use App\Models\OperatorProfile;
use App\Models\User;
use App\Support\RegisterOtp;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Laravel\Fortify\Features;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->skipUnlessFortifyHas(Features::registration());
    $this->seed([PermissionSeeder::class, RoleSeeder::class]);
});

/**
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function operatorAccountPayload(array $overrides = []): array
{
    return [
        'first_name' => 'Test',
        'last_name' => 'User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'company_name' => 'Test Vending Co',
        'phone' => '(555) 123-4567',
        'owns_machines' => true,
        'machine_types' => ['Combo', 'Snack'],
        'machines_needed' => '2',
        'primary_city' => 'Houston, TX',
        'address' => '100 Main St',
        'city' => 'Houston',
        'state' => 'TX',
        'postal_code' => '77002',
        'latitude' => 29.7604,
        'longitude' => -95.3698,
        'terms_accepted' => true,
        ...$overrides,
    ];
}

test('registration screen can be rendered', function () {
    $this->get(route('register'))->assertOk();
});

test('account create validates mismatched passwords', function () {
    Mail::fake();

    $this->postJson(route('register.account'), operatorAccountPayload([
        'password_confirmation' => 'different',
    ]))->assertUnprocessable();

    expect(User::query()->where('email', 'test@example.com')->exists())->toBeFalse();
});

test('account create stores unverified user profile and sends otp', function () {
    Mail::fake();

    $this->postJson(route('register.account'), operatorAccountPayload())
        ->assertOk()
        ->assertJson(['message' => 'Account created. Verification code sent.']);

    $this->assertAuthenticated();

    $user = User::query()->where('email', 'test@example.com')->first();

    expect($user)->not->toBeNull()
        ->and($user->hasVerifiedEmail())->toBeFalse()
        ->and($user->hasRole(RoleEnum::USER->value))->toBeTrue();

    $profile = OperatorProfile::query()->where('user_id', $user->id)->first();

    expect($profile)->not->toBeNull()
        ->and($profile->company_name)->toBe('Test Vending Co')
        ->and($profile->phone)->toBe('(555) 123-4567')
        ->and($profile->city)->toBe('Houston')
        ->and($profile->state)->toBe('TX')
        ->and($profile->postal_code)->toBe('77002')
        ->and($profile->address)->toBe('100 Main St')
        ->and((float) $profile->latitude)->toBe(29.7604)
        ->and((float) $profile->longitude)->toBe(-95.3698)
        ->and($profile->email_code_confirmed_at)->toBeNull();

    Mail::assertSent(RegisterOtpMail::class);
    expect(cache()->has(RegisterOtp::cacheKey('test@example.com')))->toBeTrue();
});

test('account create requires phone and structured location', function () {
    Mail::fake();

    $this->postJson(route('register.account'), operatorAccountPayload([
        'phone' => null,
        'city' => null,
        'state' => null,
        'latitude' => null,
        'longitude' => null,
    ]))->assertUnprocessable()
        ->assertJsonValidationErrors(['phone', 'city', 'state', 'latitude', 'longitude']);

    expect(User::query()->where('email', 'test@example.com')->exists())->toBeFalse();
});

test('account create rejects free text location without coordinates', function () {
    Mail::fake();

    $this->postJson(route('register.account'), operatorAccountPayload([
        'primary_city' => 'Houston, TX',
        'city' => 'Houston',
        'state' => 'TX',
        'latitude' => null,
        'longitude' => null,
    ]))->assertUnprocessable()
        ->assertJsonValidationErrors(['latitude', 'longitude']);
});

test('otp verify marks email verified', function () {
    Mail::fake();

    $this->postJson(route('register.account'), operatorAccountPayload())->assertOk();

    RegisterOtp::store('test@example.com', '123456');

    $this->postJson(route('register.otp.verify'), [
        'email_otp' => '123456',
    ])->assertOk();

    $user = User::query()->where('email', 'test@example.com')->first();

    expect($user->fresh()->hasVerifiedEmail())->toBeTrue()
        ->and($user->operatorProfile->fresh()->email_code_confirmed_at)->not->toBeNull();
});

test('otp verify rejects invalid code', function () {
    Mail::fake();

    $this->postJson(route('register.account'), operatorAccountPayload())->assertOk();
    RegisterOtp::store('test@example.com', '123456');

    $this->postJson(route('register.otp.verify'), [
        'email_otp' => '000000',
    ])->assertUnprocessable();

    expect(User::first()->hasVerifiedEmail())->toBeFalse();
});

test('unverified user is redirected to verification notice after login', function () {
    Mail::fake();

    $this->postJson(route('register.account'), operatorAccountPayload())->assertOk();
    $this->post(route('logout'));

    $response = $this->post(route('login.store'), [
        'email' => 'test@example.com',
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('verification.notice'));
});

test('verified operator reaches the dashboard without a payment step', function () {
    Mail::fake();

    $this->postJson(route('register.account'), operatorAccountPayload())->assertOk();
    RegisterOtp::store('test@example.com', '123456');
    $this->postJson(route('register.otp.verify'), ['email_otp' => '123456'])->assertOk();

    $this->get(route('dashboard'))->assertOk();
    $this->get('/register/payment')->assertNotFound();
    $this->post('/register/trial')->assertNotFound();
});
