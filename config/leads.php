<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Lead Price
    |--------------------------------------------------------------------------
    |
    | Flat price (in whole dollars) charged for every location lead. Payments
    | are not wired up yet, so this is only recorded on the purchase.
    |
    */

    'price' => (int) env('LEAD_PRICE', 150),

];
