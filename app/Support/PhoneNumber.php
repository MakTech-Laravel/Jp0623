<?php

namespace App\Support;

class PhoneNumber
{
    /**
     * Normalize a phone string to E.164 for Twilio.
     * US/Canada 10-digit numbers default to +1 when no country code is present.
     */
    public static function toE164(?string $phone, string $defaultCountryCode = '1'): ?string
    {
        if ($phone === null) {
            return null;
        }

        $trimmed = trim($phone);

        if ($trimmed === '') {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $trimmed) ?? '';

        if ($digits === '') {
            return null;
        }

        if (str_starts_with($trimmed, '+')) {
            if (strlen($digits) < 8 || strlen($digits) > 15) {
                return null;
            }

            return '+' . $digits;
        }

        if (strlen($digits) === 10 && $defaultCountryCode !== '') {
            return '+' . $defaultCountryCode . $digits;
        }

        if (strlen($digits) === 11 && str_starts_with($digits, $defaultCountryCode)) {
            return '+' . $digits;
        }

        if (strlen($digits) >= 8 && strlen($digits) <= 15) {
            return '+' . $digits;
        }

        return null;
    }
}
