<?php

use App\Support\PhoneNumber;

test('phone number normalizes us numbers to e164', function () {
    expect(PhoneNumber::toE164('(555) 123-4567'))->toBe('+15551234567')
        ->and(PhoneNumber::toE164('15551234567'))->toBe('+15551234567')
        ->and(PhoneNumber::toE164('+1 555 123 4567'))->toBe('+15551234567');
});

test('phone number returns null for invalid values', function () {
    expect(PhoneNumber::toE164(null))->toBeNull()
        ->and(PhoneNumber::toE164(''))->toBeNull()
        ->and(PhoneNumber::toE164('123'))->toBeNull();
});
