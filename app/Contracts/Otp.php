<?php

namespace App\Contracts;

interface Otp
{
    public function send(string $phone): void;

    public function verify(string $phone, string $otp): bool;
}
