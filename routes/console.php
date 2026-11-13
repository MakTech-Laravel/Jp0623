<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Settle lead purchases whose Square outcome was unknown (needs the scheduler cron).
Schedule::command('payments:reconcile')->everyFiveMinutes()->withoutOverlapping();

// Charge due operator memberships (card on file).
Schedule::command('memberships:bill')->daily()->withoutOverlapping();
