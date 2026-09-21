<?php

namespace App\Actions;

use App\Models\Sa96Registration;
use App\Support\Sa96Privacy;

class WithdrawSa96Visitor
{
    public function execute(string $phoneE164): void
    {
        $registration = Sa96Registration::query()
            ->active()
            ->where('phone_hash', Sa96Privacy::phoneHash($phoneE164))
            ->first();

        if ($registration === null) {
            return;
        }

        $registration->forceFill([
            'name' => null,
            'phone' => null,
            'birth_year' => null,
            'ip_address' => null,
            'user_agent' => null,
            'withdrawn_at' => now(),
        ])->save();
    }
}
