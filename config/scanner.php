<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Scanner Access PIN
    |--------------------------------------------------------------------------
    |
    | The PIN required to unlock the public voucher scanner page. Guests can
    | redeem vouchers after entering this code — no username or password.
    |
    */

    'pin' => env('SCANNER_PIN', '0000'),

];
