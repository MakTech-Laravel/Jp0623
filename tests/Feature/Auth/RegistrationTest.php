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
        'owns_machines' => true,
        'machine_types' => ['Combo', 'Snack'],
        'machines_needed' => '2',
        'primary_city' => 'Houston, TX',
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
        ->and($profile->email_code_confirmed_at)->toBeNull()
        ->and($profile->trial_started_at)->toBeNull();

    Mail::assertSent(RegisterOtpMail::class);
    expect(cache()->has(RegisterOtp::cacheKey('test@example.com')))->toBeTrue();
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

test('trial continue sets trial and redirects to dashboard', function () {
    Mail::fake();

    $this->postJson(route('register.account'), operatorAccountPayload())->assertOk();
    RegisterOtp::store('test@example.com', '123456');
    $this->postJson(route('register.otp.verify'), ['email_otp' => '123456'])->assertOk();

    $this->post(route('register.trial'))
        ->assertRedirect(route('dashboard'));

    expect(User::first()->operatorProfile->fresh()->trial_started_at)->not->toBeNull();
});
