<?php

namespace App\Services;

use App\Enums\OperatorSmsStatus;
use App\Enums\SmsTemplatePurpose;
use App\Models\CoffeeServiceRequest;
use App\Models\OperatorProfile;
use App\Models\VendingRequest;
use App\Support\PhoneNumber;
use App\Support\SmsTemplateRenderer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Throwable;

class NearbyOperatorLeadNotifier
{
    public function __construct(
        private NearbyOperatorLocator $locator,
        private TwilioSmsService $twilio,
        private SmsTemplateService $templates,
    ) {}

    public function notifyForVendingRequest(VendingRequest $request): OperatorSmsStatus
    {
        if ($request->latitude === null || $request->longitude === null) {
            return $this->markLead($request, OperatorSmsStatus::SKIPPED, 0, 0, null, 'Lead has no map coordinates.');
        }

        $template = $this->templates->activeFor(SmsTemplatePurpose::AUTO_VENDING);
        $fallback = 'New vending lead near you: {{business_name}}, {{location}}. Contact {{contact_name}} at {{phone}}.';
        $message = SmsTemplateRenderer::forVendingRequest(
            $template?->body ?? $fallback,
            $request,
        );

        return $this->notifyOperators(
            $request,
            (float) $request->latitude,
            (float) $request->longitude,
            $message,
            [
                'lead_type' => 'vending',
                'lead_id' => $request->id,
            ],
        );
    }

    public function notifyForCoffeeServiceRequest(CoffeeServiceRequest $request): OperatorSmsStatus
    {
        if ($request->latitude === null || $request->longitude === null) {
            return $this->markLead($request, OperatorSmsStatus::SKIPPED, 0, 0, null, 'Lead has no map coordinates.');
        }

        $template = $this->templates->activeFor(SmsTemplatePurpose::AUTO_COFFEE);
        $fallback = 'New coffee service lead near you: {{business_name}}, {{location}}. Contact {{contact_name}} at {{phone}}.';
        $message = SmsTemplateRenderer::forCoffeeServiceRequest(
            $template?->body ?? $fallback,
            $request,
        );

        return $this->notifyOperators(
            $request,
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
        Model $lead,
        float $latitude,
        float $longitude,
        string $message,
        array $context,
    ): OperatorSmsStatus {
        if (! $this->twilio->isEnabled()) {
            Log::info('Skipped nearby operator SMS; Twilio is disabled or not configured.', $context);

            return $this->markLead(
                $lead,
                OperatorSmsStatus::SKIPPED,
                0,
                0,
                null,
                'Twilio SMS is disabled or not configured.',
            );
        }

        $radiusMiles = (float) config('services.twilio.lead_radius_miles', 100);
        $operators = $this->locator->withinMiles($latitude, $longitude, $radiusMiles);

        if ($operators->isEmpty()) {
            return $this->markLead(
                $lead,
                OperatorSmsStatus::SKIPPED,
                0,
                0,
                null,
                "No operators found within {$radiusMiles} miles.",
            );
        }

        $sent = 0;
        $failed = 0;
        $providerStatus = null;

        foreach ($operators as $operator) {
            $result = $this->sendToOperator($operator, $message, $context);

            if ($result['ok']) {
                $sent++;
                $providerStatus = $result['provider_status'] ?? $providerStatus;
            } else {
                $failed++;
            }
        }

        $status = match (true) {
            $sent > 0 && $failed === 0 => OperatorSmsStatus::DONE,
            $sent > 0 && $failed > 0 => OperatorSmsStatus::PARTIAL,
            default => OperatorSmsStatus::FAILED,
        };

        $note = match ($status) {
            OperatorSmsStatus::DONE => "Notified {$sent} nearby operator" . ($sent === 1 ? '' : 's') . '.',
            OperatorSmsStatus::PARTIAL => "Notified {$sent} operator" . ($sent === 1 ? '' : 's') . "; {$failed} failed.",
            default => "Failed to notify {$failed} nearby operator" . ($failed === 1 ? '' : 's') . '.',
        };

        Log::info('Nearby operator SMS fan-out finished.', [
            ...$context,
            'candidates' => $operators->count(),
            'sent' => $sent,
            'failed' => $failed,
            'status' => $status->value,
            'radius_miles' => $radiusMiles,
        ]);

        return $this->markLead($lead, $status, $sent, $failed, $providerStatus, $note);
    }

    /**
     * @param  array{lead_type: string, lead_id: int}  $context
     * @return array{ok: bool, provider_status: string|null}
     */
    private function sendToOperator(OperatorProfile $operator, string $message, array $context): array
    {
        $to = PhoneNumber::toE164($operator->phone);

        if ($to === null) {
            Log::warning('Skipped operator SMS; phone could not be normalized.', [
                ...$context,
                'operator_profile_id' => $operator->id,
                'phone' => $operator->phone,
            ]);

            return ['ok' => false, 'provider_status' => null];
        }

        try {
            $result = $this->twilio->send($to, $message);

            return [
                'ok' => true,
                'provider_status' => $result['status'],
            ];
        } catch (Throwable $exception) {
            Log::warning('Failed to send nearby operator SMS.', [
                ...$context,
                'operator_profile_id' => $operator->id,
                'to' => $to,
                'error' => $exception->getMessage(),
            ]);

            return ['ok' => false, 'provider_status' => null];
        }
    }

    private function markLead(
        Model $lead,
        OperatorSmsStatus $status,
        int $sent,
        int $failed,
        ?string $providerStatus,
        string $note,
    ): OperatorSmsStatus {
        $lead->forceFill([
            'operator_sms_status' => $status->value,
            'operator_sms_sent_count' => $sent,
            'operator_sms_failed_count' => $failed,
            'operator_sms_provider_status' => $providerStatus,
            'operator_sms_note' => $note,
            'operator_sms_notified_at' => now(),
        ])->save();

        return $status;
    }
}
