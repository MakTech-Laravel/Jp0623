<?php

namespace App\Services;

use App\Models\CoffeeServiceRequest;
use App\Models\OperatorProfile;
use App\Models\VendingRequest;
use App\Support\PhoneNumber;
use Illuminate\Support\Facades\Log;
use Throwable;

class NearbyOperatorLeadNotifier
{
    public function __construct(
        private NearbyOperatorLocator $locator,
        private TwilioSmsService $twilio,
    ) {}

    public function notifyForVendingRequest(VendingRequest $request): int
    {
        if ($request->latitude === null || $request->longitude === null) {
            return 0;
        }

        $location = $this->formatLocation(
            $request->city,
            $request->state,
            $request->address_or_zip,
        );

        $message = sprintf(
            'New vending lead near you: %s%s. Contact %s at %s.',
            $request->business_name,
            $location !== '' ? ", {$location}" : '',
            $request->contact_name,
            $request->phone,
        );

        return $this->notifyOperators(
            (float) $request->latitude,
            (float) $request->longitude,
            $message,
            [
                'lead_type' => 'vending',
                'lead_id' => $request->id,
            ],
        );
    }

    public function notifyForCoffeeServiceRequest(CoffeeServiceRequest $request): int
    {
        if ($request->latitude === null || $request->longitude === null) {
            return 0;
        }

        $location = $this->formatLocation(
            $request->city,
            $request->state,
            $request->zip_code,
        );

        $message = sprintf(
            'New coffee service lead near you: %s%s. Contact %s at %s.',
            $request->business_name,
            $location !== '' ? ", {$location}" : '',
            $request->contact_name,
            $request->phone,
        );

        return $this->notifyOperators(
            (float) $request->latitude,
            (float) $request->longitude,
            $message,
            [
                'lead_type' => 'coffee',
                'lead_id' => $request->id,
            ],
        );
    }

    /**
     * @param  array{lead_type: string, lead_id: int}  $context
     */
    private function notifyOperators(
        float $latitude,
        float $longitude,
        string $message,
        array $context,
    ): int {
        if (! $this->twilio->isEnabled()) {
            Log::info('Skipped nearby operator SMS; Twilio is disabled or not configured.', $context);

            return 0;
        }

        $radiusMiles = (float) config('services.twilio.lead_radius_miles', 100);
        $operators = $this->locator->withinMiles($latitude, $longitude, $radiusMiles);
        $sent = 0;

        foreach ($operators as $operator) {
            if ($this->sendToOperator($operator, $message, $context)) {
                $sent++;
            }
        }

        Log::info('Nearby operator SMS fan-out finished.', [
            ...$context,
            'candidates' => $operators->count(),
            'sent' => $sent,
            'radius_miles' => $radiusMiles,
        ]);

        return $sent;
    }

    /**
     * @param  array{lead_type: string, lead_id: int}  $context
     */
    private function sendToOperator(OperatorProfile $operator, string $message, array $context): bool
    {
        $to = PhoneNumber::toE164($operator->phone);

        if ($to === null) {
            Log::warning('Skipped operator SMS; phone could not be normalized.', [
                ...$context,
                'operator_profile_id' => $operator->id,
                'phone' => $operator->phone,
            ]);

            return false;
        }

        try {
            $this->twilio->send($to, $message);

            return true;
        } catch (Throwable $exception) {
            Log::warning('Failed to send nearby operator SMS.', [
                ...$context,
                'operator_profile_id' => $operator->id,
                'to' => $to,
                'error' => $exception->getMessage(),
            ]);

            return false;
        }
    }

    private function formatLocation(?string $city, ?string $state, ?string $fallback = null): string
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
