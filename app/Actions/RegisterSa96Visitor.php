<?php

namespace App\Actions;

use App\Models\Sa96Registration;
use App\Support\Sa96Privacy;
use Illuminate\Http\Request;

class RegisterSa96Visitor
{
    /**
     * @param  array{
     *     name: string,
     *     birth_year: int,
     *     phone_e164: string,
     *     locale: string,
     *     ip_address?: ?string,
     *     user_agent?: ?string
     * }  $data
     */
    public function execute(array $data, ?Request $request = null): Sa96Registration
    {
        $phone = $data['phone_e164'];
        $locale = $data['locale'];

        return Sa96Registration::query()->updateOrCreate(
            ['phone_hash' => Sa96Privacy::phoneHash($phone)],
            [
                'name' => $data['name'],
                'phone' => $phone,
                'birth_year' => (int) $data['birth_year'],
                'locale' => $locale,
                'consent_privacy_notice' => true,
                'consent_campaign' => true,
                'consent_capacity' => true,
                'consent_cross_border' => true,
                'consent_marketing' => true,
                'consent_notice_version' => config('sa96.notice_version'),
                'consent_method' => 'web_form_otp',
                'consent_snapshot' => config('sa96.consents.'.$locale),
                'consented_at' => now(),
                'ip_address' => $data['ip_address'] ?? $request?->ip(),
                'user_agent' => $data['user_agent'] ?? mb_substr((string) $request?->userAgent(), 0, 512),
                'withdrawn_at' => null,
            ],
        );
    }
}
