<?php

namespace App\Jobs;

use App\Models\OperatorProfile;
use App\Services\TwilioSmsService;
use App\Support\PhoneNumber;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

class SendAdminOperatorSms implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    /**
     * @var list<int>
     */
    public array $backoff = [10, 30, 60];

    /**
     * @param  list<int>  $operatorProfileIds
     */
    public function __construct(
        public array $operatorProfileIds,
        public string $message,
    ) {
        $this->afterCommit = true;
    }

    public function handle(TwilioSmsService $twilio): void
    {
        if (! $twilio->isEnabled()) {
            Log::info('Skipped admin operator SMS; Twilio is disabled or not configured.', [
                'operator_count' => count($this->operatorProfileIds),
            ]);

            return;
        }

        $operators = OperatorProfile::query()
            ->whereIn('id', $this->operatorProfileIds)
            ->whereNotNull('phone')
            ->where('phone', '!=', '')
            ->orderBy('id')
            ->get(['id', 'phone']);

        $sent = 0;

        foreach ($operators as $operator) {
            $to = PhoneNumber::toE164($operator->phone);

            if ($to === null) {
                Log::warning('Skipped admin operator SMS; phone could not be normalized.', [
                    'operator_profile_id' => $operator->id,
                    'phone' => $operator->phone,
                ]);

                continue;
            }

            try {
                $twilio->send($to, $this->message);
                $sent++;
            } catch (Throwable $exception) {
                Log::warning('Failed to send admin operator SMS.', [
                    'operator_profile_id' => $operator->id,
                    'to' => $to,
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        Log::info('Admin operator SMS fan-out finished.', [
            'requested' => count($this->operatorProfileIds),
            'candidates' => $operators->count(),
            'sent' => $sent,
        ]);
    }

    public function failed(?Throwable $exception): void
    {
        Log::error('Admin operator SMS job failed permanently.', [
            'operator_count' => count($this->operatorProfileIds),
            'error' => $exception?->getMessage(),
        ]);
    }
}
