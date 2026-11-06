<?php

namespace App\Http\Requests\User;

use App\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    /**
     * Only a super-admin may modify a super-admin account (UserPolicy::update).
     * Cross-scope targets are rejected with 404 before validation.
     */
    public function authorize(): bool
    {
        /** @var User $target */
        $target = $this->route('user');

        if ($this->routeIs('admin.user-management.*') && ! $target->hasRole(RoleEnum::USER->value)) {
            abort(404);
        }

        if ($this->routeIs('admin.admin-management.*') && ! $target->hasRole(RoleEnum::ADMIN->value)) {
            abort(404);
        }

        return $this->user()->can('update', $target);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'email' => 'required|email|unique:users,email,'.$this->route('user')->id,
            'password' => 'nullable|min:8',
            'avatar' => 'nullable|image|max:2048',
            'remove_avatar' => 'nullable|boolean',
            'roles' => 'nullable|array',
            'roles.*' => 'string|exists:roles,name',
        ];

        if ($this->isUserManagement()) {
            return [
                ...$rules,
                'name' => 'nullable|string|max:255',
                'first_name' => ['required', 'string', 'max:255'],
                'last_name' => ['required', 'string', 'max:255'],
                'company_name' => ['required', 'string', 'max:255'],
                'owns_machines' => ['required', 'boolean'],
                'machine_types' => ['required', 'array', 'min:1'],
                'machine_types.*' => ['string', 'max:100'],
                'machines_needed' => ['required', 'string', Rule::in(['1', '2', '3', '4', '5', '6+'])],
                'primary_city' => ['required', 'string', 'max:255'],
            ];
        }

        return [
            ...$rules,
            'name' => 'required|string|max:255',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'The name field is required.',
            'email.required' => 'The email field is required.',
            'email.email' => 'The email field must be a valid email address.',
            'email.unique' => 'The email address has already been taken.',
            'password.min' => 'The password field must be at least 8 characters long.',
            'avatar.image' => 'The avatar field must be an image.',
            'avatar.max' => 'The avatar field must be less than 2048 kilobytes.',
            'roles.*.exists' => 'One of the selected roles is invalid.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'Name',
            'email' => 'Email',
            'password' => 'Password',
            'avatar' => 'Avatar',
            'roles' => 'Roles',
            'first_name' => 'First name',
            'last_name' => 'Last name',
            'company_name' => 'Company name',
            'owns_machines' => 'Owns machines',
            'machine_types' => 'Machine types',
            'machines_needed' => 'Machines needed',
            'primary_city' => 'Primary city',
        ];
    }

    private function isUserManagement(): bool
    {
        return $this->routeIs('admin.user-management.*');
    }
}
