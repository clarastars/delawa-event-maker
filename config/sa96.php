<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Saudi National Day 96 campaign (PDPL)
    |--------------------------------------------------------------------------
    |
    | Notice version is stored with every consent record. Bump this string
    | whenever the privacy notice or consent wording changes.
    |
    */

    'notice_version' => '2026-09-21.3',

    'min_age' => 18,

    'retention_days' => (int) env('SA96_RETENTION_DAYS', 365),

    'withdrawn_retention_days' => (int) env('SA96_WITHDRAWN_RETENTION_DAYS', 30),

    'controller_name' => env('SA96_CONTROLLER_NAME', 'Delawa'),

    'controller_legal_name' => env('SA96_CONTROLLER_LEGAL_NAME', 'Delawa (Adv Line)'),

    'privacy_email' => env('SA96_PRIVACY_EMAIL', 'privacy@adv-line.sa'),

    'sdaia_complaints_url' => 'https://sdaia.gov.sa',

    'consents' => [
        'en' => [
            'agree' => 'I agree to using my mobile number to complete verification. Delawa may send me ads or offers, and I can opt out and delete my data at any time.',
        ],
        'ar' => [
            'agree' => 'أوافق على استخدام رقم جوالي لإكمال عملية التحقق. ديلاوة قد ترسل لي إعلانات أو عروض لديها، ويمكنني الإلغاء وحذف بياناتي في أي وقت.',
        ],
    ],

];
