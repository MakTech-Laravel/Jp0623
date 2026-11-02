<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;

class RegisterOtp
{
    public const TTL_SECONDS = 600;

    public static function cacheKey(string $email): string
    {
        return 'register-otp:'.strtolower(trim($email));
    }

    public static function store(string $email, string $code): void
    {
        Cache::put(
            self::cacheKey($email),
            Hash::make($code),
            now()->addSeconds(self::TTL_SECONDS),
        );
    }

    public static function verify(string $email, string $code): bool
    {
        $hash = Cache::get(self::cacheKey($email));

        if (! is_string($hash) || $hash === '') {
            return false;
        }

        return Hash::check($code, $hash);
    }

    public static function forget(string $email): void
    {
        Cache::forget(self::cacheKey($email));
    }

    public static function generateCode(): string
    {
        return str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }
}
