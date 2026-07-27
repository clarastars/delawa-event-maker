<?php

namespace App\Services\Otp;

use App\Contracts\Otp;

class DebugOtp implements Otp
{
    public function send(string $phone): void
    {
        //
    }

    public function verify(string $phone, string $otp): bool
    {
        $otp = preg_replace('/\D+/', '', $otp) ?? '';

        return $otp === (string) config('services.authentica.debug_otp_code', '1234');
    }
}
