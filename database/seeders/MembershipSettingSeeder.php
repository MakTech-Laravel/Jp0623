<?php

namespace Database\Seeders;

use App\Enums\RoleEnum;
use App\Models\MembershipSetting;
use App\Models\OperatorMembership;
use App\Models\User;
use App\Services\MembershipSettingsService;
use Illuminate\Database\Seeder;

class MembershipSettingSeeder extends Seeder
{
    public function run(): void
    {
        MembershipSetting::query()->firstOrCreate([], [
            'mode' => MembershipSetting::MODE_FREE,
            'monthly_price' => (int) config('membership.default_monthly_price', 29),
            'grace_days' => 0,
        ]);

        app(MembershipSettingsService::class)->forgetCache();

        $settings = app(MembershipSettingsService::class)->current();

        User::role(RoleEnum::USER->value)
            ->whereDoesntHave('membership')
            ->each(function (User $user) use ($settings): void {
                OperatorMembership::query()->create([
                    'user_id' => $user->id,
                    'status' => $settings->isPaid()
                        ? OperatorMembership::STATUS_REQUIRED
                        : OperatorMembership::STATUS_ACTIVE_FREE,
                    'monthly_price' => (int) $settings->monthly_price,
                ]);
            });
    }
}
