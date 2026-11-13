<?php

namespace App\Console\Commands;

use App\Services\MembershipBillingService;
use Illuminate\Console\Command;

class BillMembershipsCommand extends Command
{
    protected $signature = 'memberships:bill';

    protected $description = 'Charge due operator memberships through Square';

    public function handle(MembershipBillingService $billing): int
    {
        $result = $billing->billDue();

        $this->info(sprintf(
            'Membership billing finished: %d charged, %d failed, %d skipped.',
            $result['charged'],
            $result['failed'],
            $result['skipped'],
        ));

        return self::SUCCESS;
    }
}
