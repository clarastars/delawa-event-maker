<?php

namespace App\Contracts;

interface GiftCardBalance
{
    public function remainingBalance(string $cardNumber): ?float;
}
