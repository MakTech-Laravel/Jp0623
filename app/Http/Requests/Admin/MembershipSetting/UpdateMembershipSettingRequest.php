<?php

namespace App\Http\Requests\Admin\MembershipSetting;

use App\Models\MembershipSetting;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMembershipSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'mode' => ['required', 'string', Rule::in(MembershipSetting::MODES)],
            'monthly_price' => ['required', 'integer', 'min:1', 'max:999999'],
            'grace_days' => ['nullable', 'integer', 'min:0', 'max:90'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'mode' => 'membership mode',
            'monthly_price' => 'monthly price',
            'grace_days' => 'grace days',
        ];
    }
}
