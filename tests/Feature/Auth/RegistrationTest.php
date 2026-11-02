<?php

use App\Mail\RegisterOtpMail;
use App\Models\OperatorProfile;
use App\Models\User;
use App\Support\RegisterOtp;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Laravel\Fortify\Features;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->skipUnlessFortifyHas(Features::registration());
});

test('registration screen can be rendered', function () {
    $response = $this->get(route('register'));

    $response->assertOk();
});

test('register otp endpoint sends mail and stores code', function () {
    Mail::fake();

    $this->postJson(route('register.otp'), [
        'email' => 'test@example.com',
    ])
        ->assertOk()
        ->assertJson(['message' => 'Verification code sent.']);

    Mail::assertSent(RegisterOtpMail::class, function (RegisterOtpMail $mail): bool {
        return $mail->hasTo('test@example.com')
            && strlen($mail->code) === 6;
    });

    expect(cache()->has(RegisterOtp::cacheKey('test@example.com')))->toBeTrue();
});

test('new users cannot register with an invalid otp', function () {
    RegisterOtp::store('test@example.com', '123456');

    $this->post(route('register.store'), operatorPayload([
        'email_otp' => '000000',
    ]))->assertSessionHasErrors('email_otp');

    $this->assertGuest();
    expect(User::query()->where('email', 'test@example.com')->exists())->toBeFalse();
});

test('new users can register with operator profile and valid otp', function () {
    RegisterOtp::store('test@example.com', '123456');

    $response = $this->post(route('register.store'), operatorPayload([
        'email_otp' => '123456',
    ]));

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));

    $user = User::query()->where('email', 'test@example.com')->first();

    expect($user)->not->toBeNull()
        ->and($user->name)->toBe('Test User');

    $profile = OperatorProfile::query()->where('user_id', $user->id)->first();

    expect($profile)->not->toBeNull()
        ->and($profile->first_name)->toBe('Test')
        ->and($profile->last_name)->toBe('User')
        ->and($profile->company_name)->toBe('Test Vending Co')
        ->and($profile->owns_machines)->toBeTrue()
        ->and($profile->machine_types)->toBe(['Combo', 'Snack'])
        ->and($profile->machines_needed)->toBe('2')
        ->and($profile->primary_city)->toBe('Houston, TX')
        ->and($profile->terms_accepted_at)->not->toBeNull()
        ->and($profile->email_code_confirmed_at)->not->toBeNull()
        ->and($profile->trial_started_at)->not->toBeNull();

    expect(cache()->has(RegisterOtp::cacheKey('test@example.com')))->toBeFalse();
});

/**
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function operatorPayload(array $overrides = []): array
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
        'email_otp' => '123456',
        'start_trial' => true,
        ...$overrides,
    ];
}
