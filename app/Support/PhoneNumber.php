<?php

namespace App\Support;

use App\Models\Contact;

class PhoneNumber
{
    public static function toE164(string $phone): ?string
    {
        $digits = Contact::normalizePhone($phone);

        if (str_starts_with($digits, '966') && strlen($digits) >= 11) {
            return '+'.$digits;
        }

        if (str_starts_with($digits, '0') && strlen($digits) === 10) {
            return '+966'.substr($digits, 1);
        }

        if (str_starts_with($digits, '5') && strlen($digits) === 9) {
            return '+966'.$digits;
        }

        $trimmed = trim($phone);

        if (preg_match('/^\+[1-9]\d{6,14}$/', $trimmed) === 1) {
            return $trimmed;
        }

        return null;
    }

    public static function isE164(string $phone): bool
    {
        return preg_match('/^\+[1-9]\d{6,14}$/', $phone) === 1;
    }
}
