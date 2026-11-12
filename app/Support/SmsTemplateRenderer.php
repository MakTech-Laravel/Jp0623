<?php

namespace App\Support;

use App\Models\CoffeeServiceRequest;
use App\Models\VendingRequest;

class SmsTemplateRenderer
{
    /**
     * Placeholders available in SMS templates.
     *
     * @return list<array{key: string, label: string, example: string}>
     */
    public static function availableVariables(): array
    {
        return [
            ['key' => 'business_name', 'label' => 'Business name', 'example' => 'Riverside Logistics'],
            ['key' => 'contact_name', 'label' => 'Contact name', 'example' => 'Jane Doe'],
            ['key' => 'phone', 'label' => 'Contact phone', 'example' => '(555) 555-5555'],
            ['key' => 'email', 'label' => 'Contact email', 'example' => 'jane@example.com'],
            ['key' => 'location', 'label' => 'City / address', 'example' => 'Houston, TX'],
            ['key' => 'lead_type', 'label' => 'Lead type', 'example' => 'vending'],
        ];
    }

    /**
     * @param  array<string, string|null>  $replacements
     */
    public static function render(string $body, array $replacements): string
    {
        $message = $body;

        foreach ($replacements as $key => $value) {
            $message = str_replace(
                ['{{'.$key.'}}', '{'.$key.'}'],
                (string) ($value ?? ''),
                $message,
            );
        }

        return trim(preg_replace('/\s+/', ' ', $message) ?? $message);
    }

    public static function forVendingRequest(string $body, VendingRequest $request): string
    {
        $location = self::composeLocation(
            $request->city,
            $request->state,
            $request->address_or_zip,
        );

        return self::render($body, [
            'business_name' => $request->business_name,
            'contact_name' => $request->contact_name,
            'phone' => $request->phone,
            'email' => $request->email,
            'location' => $location,
            'lead_type' => 'vending',
        ]);
    }

    public static function forCoffeeServiceRequest(string $body, CoffeeServiceRequest $request): string
    {
        $location = self::composeLocation(
            $request->city,
            $request->state,
            $request->zip_code,
        );

        return self::render($body, [
            'business_name' => $request->business_name,
            'contact_name' => $request->contact_name,
            'phone' => $request->phone,
            'email' => $request->email,
            'location' => $location,
            'lead_type' => 'coffee',
        ]);
    }

    private static function composeLocation(?string $city, ?string $state, ?string $fallback = null): string
    {
        $parts = array_values(array_filter([
            filled($city) ? trim((string) $city) : null,
            filled($state) ? trim((string) $state) : null,
        ]));

        if ($parts !== []) {
            return implode(', ', $parts);
        }

        return filled($fallback) ? trim((string) $fallback) : '';
    }
}
