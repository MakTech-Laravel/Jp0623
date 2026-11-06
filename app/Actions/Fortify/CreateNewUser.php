<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Enums\RoleEnum;
use App\Models\User;
use App\Support\RegisterOtp;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules, ProfileValidationRules;

    /**
     * Validate and create a newly registered user with operator profile.
     *
     * @param  array<string, mixed>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => $this->emailRules(),
            'password' => $this->passwordRules(),
            'company_name' => ['required', 'string', 'max:255'],
            'owns_machines' => ['required', 'boolean'],
            'machine_types' => ['required', 'array', 'min:1'],
            'machine_types.*' => ['string', 'max:100'],
            'machines_needed' => ['required', 'string', Rule::in(['1', '2', '3', '4', '5', '6+'])],
            'primary_city' => ['required', 'string', 'max:255'],
            'terms_accepted' => ['accepted'],
            'email_otp' => ['required', 'digits:6'],
            'start_trial' => ['required', 'boolean'],
        ])->validate();

        $email = strtolower(trim((string) $input['email']));
        $otp = (string) $input['email_otp'];

        if (! RegisterOtp::verify($email, $otp)) {
            throw ValidationException::withMessages([
                'email_otp' => __('The verification code is invalid or has expired.'),
            ]);
        }

        $now = now();

        return DB::transaction(function () use ($input, $email, $now): User {
            RegisterOtp::forget($email);

            $user = User::create([
                'name' => trim($input['first_name'].' '.$input['last_name']),
                'email' => $input['email'],
                'password' => $input['password'],
            ]);

            $user->operatorProfile()->create([
                'first_name' => $input['first_name'],
                'last_name' => $input['last_name'],
                'company_name' => $input['company_name'],
                'owns_machines' => $input['owns_machines'],
                'machine_types' => $input['machine_types'],
                'machines_needed' => $input['machines_needed'],
                'primary_city' => $input['primary_city'],
                'terms_accepted_at' => $now,
                'email_code_confirmed_at' => $now,
                'trial_started_at' => filter_var($input['start_trial'], FILTER_VALIDATE_BOOLEAN)
                    ? $now
                    : null,
            ]);

            $user->assignRole(RoleEnum::USER->value);

            return $user;
        });
    }
}
