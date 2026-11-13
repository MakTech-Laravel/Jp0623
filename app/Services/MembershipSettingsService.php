<?php

namespace App\Services;

use App\Models\MembershipSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class MembershipSettingsService
{
    private const CACHE_KEY = 'membership.settings';

    public function current(): MembershipSetting
    {
        $cached = Cache::get(self::CACHE_KEY);

        // Old entries stored a serialized Eloquent model; database/file drivers can
        // return __PHP_Incomplete_Class. Only accept plain attribute arrays.
        if (! is_array($cached)) {
            Cache::forget(self::CACHE_KEY);
            $cached = null;
        }

        if (is_array($cached)) {
            return (new MembershipSetting)->newFromBuilder($cached);
        }

        $settings = MembershipSetting::query()->first();

        if ($settings === null) {
            $settings = MembershipSetting::query()->create([
                'mode' => MembershipSetting::MODE_FREE,
                'monthly_price' => (int) config('membership.default_monthly_price', 29),
                'grace_days' => 0,
            ]);
        }

        Cache::forever(self::CACHE_KEY, $settings->getAttributes());

        return $settings;
    }

    public function isPaid(): bool
    {
        return $this->current()->isPaid();
    }

    public function isFree(): bool
    {
        return $this->current()->isFree();
    }

    public function monthlyPrice(): int
    {
        return max(1, (int) $this->current()->monthly_price);
    }

    /**
     * @param  array{mode: string, monthly_price: int, grace_days?: int}  $data
     */
    public function update(array $data): MembershipSetting
    {
        return DB::transaction(function () use ($data): MembershipSetting {
            $settings = MembershipSetting::query()->lockForUpdate()->first()
                ?? MembershipSetting::query()->create([
                    'mode' => MembershipSetting::MODE_FREE,
                    'monthly_price' => (int) config('membership.default_monthly_price', 29),
                    'grace_days' => 0,
                ]);

            $settings->fill([
                'mode' => $data['mode'],
                'monthly_price' => max(1, (int) $data['monthly_price']),
                'grace_days' => max(0, (int) ($data['grace_days'] ?? $settings->grace_days)),
            ])->save();

            // Mode flips update settings only — operator membership statuses and
            // paid periods are preserved so paying operators are not re-billed early.

            Cache::forget(self::CACHE_KEY);

            return $settings->refresh();
        });
    }

    public function forgetCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
