<?php

namespace App\Support;

class Sa96Privacy
{
    public static function phoneHash(string $e164): string
    {
        return hash_hmac('sha256', $e164, (string) config('app.key'));
    }

    public static function minBirthYear(): int
    {
        return now()->year - 120;
    }

    public static function maxBirthYear(): int
    {
        return now()->year - (int) config('sa96.min_age');
    }
}
