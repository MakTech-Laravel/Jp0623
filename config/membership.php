<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Monthly Price
    |--------------------------------------------------------------------------
    |
    | Used when seeding membership_settings and as a fallback before the
    | admin configures a paid plan. Whole dollars.
    |
    */

    'default_monthly_price' => (int) env('MEMBERSHIP_MONTHLY_PRICE', 29),

];
