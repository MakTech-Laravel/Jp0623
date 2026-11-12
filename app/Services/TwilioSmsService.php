<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class TwilioSmsService
{
    public function isEnabled(): bool
    {
        return (bool) config('services.twilio.enabled')
            && filled(config('services.twilio.sid'))
            && filled(config('services.twilio.token'))
            && filled(config('services.twilio.from'));
    }

    /**
     * @throws RuntimeException
     * @throws ConnectionException
     */
    public function send(string $toE164, string $body): void
    {
        if (! $this->isEnabled()) {
            throw new RuntimeException('Twilio SMS is not configured.');
        }

        $sid = (string) config('services.twilio.sid');
        $token = (string) config('services.twilio.token');
        $from = (string) config('services.twilio.from');
        $sandboxPrefix = trim((string) config('services.twilio.sandbox_message_prefix', ''));

        if ($sandboxPrefix !== '' && ! str_starts_with($body, $sandboxPrefix)) {
            $body = trim($sandboxPrefix . ' ' . $body);
        }

        $payload = [
            'To' => $toE164,
            'From' => $from,
            'Body' => $body,
        ];

        $messagingServiceSid = config('services.twilio.messaging_service_sid');

        if (filled($messagingServiceSid)) {
            unset($payload['From']);
            $payload['MessagingServiceSid'] = (string) $messagingServiceSid;
        }

        $response = Http::withBasicAuth($sid, $token)
            ->asForm()
            ->acceptJson()
            ->connectTimeout(5)
            ->timeout(15)
            ->retry(2, 500, fn(\Throwable $exception): bool => $exception instanceof ConnectionException)
            ->post("https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json", $payload);

        if ($response->failed()) {
            Log::warning('Twilio SMS send failed.', [
                'to' => $toE164,
                'status' => $response->status(),
                'body' => $response->json() ?? $response->body(),
            ]);

            $response->throw();
        }
    }
}
