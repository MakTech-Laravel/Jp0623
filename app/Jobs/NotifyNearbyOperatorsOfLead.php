<?php

namespace App\Jobs;

use App\Models\CoffeeServiceRequest;
use App\Models\VendingRequest;
use App\Services\NearbyOperatorLeadNotifier;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Facades\Log;
use Throwable;

class NotifyNearbyOperatorsOfLead implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    /**
     * @var list<int>
     */
    public array $backoff = [10, 30, 60];

    public function __construct(
        public string $leadType,
        public int $leadId,
    ) {
        $this->afterCommit = true;
    }

    /**
     * @return list<object>
     */
    public function middleware(): array
    {
        return [
            (new WithoutOverlapping("nearby-operator-sms:{$this->leadType}:{$this->leadId}"))
                ->expireAfter(120)
                ->releaseAfter(30),
        ];
    }

    public function handle(NearbyOperatorLeadNotifier $notifier): void
    {
        match ($this->leadType) {
            'vending' => $this->notifyVending($notifier),
            'coffee' => $this->notifyCoffee($notifier),
            default => Log::warning('Unknown lead type for nearby operator SMS.', [
                'lead_type' => $this->leadType,
                'lead_id' => $this->leadId,
            ]),
        };
    }

    public function failed(?Throwable $exception): void
    {
        Log::error('Nearby operator SMS job failed permanently.', [
            'lead_type' => $this->leadType,
            'lead_id' => $this->leadId,
            'error' => $exception?->getMessage(),
        ]);
    }

    private function notifyVending(NearbyOperatorLeadNotifier $notifier): void
    {
        $request = VendingRequest::query()->find($this->leadId);

        if ($request === null) {
            Log::warning('Vending request missing for nearby operator SMS.', [
                'lead_id' => $this->leadId,
            ]);

            return;
        }

        $notifier->notifyForVendingRequest($request);
    }

    private function notifyCoffee(NearbyOperatorLeadNotifier $notifier): void
    {
        $request = CoffeeServiceRequest::query()->find($this->leadId);

        if ($request === null) {
            Log::warning('Coffee service request missing for nearby operator SMS.', [
                'lead_id' => $this->leadId,
            ]);

            return;
        }

        $notifier->notifyForCoffeeServiceRequest($request);
    }
}
