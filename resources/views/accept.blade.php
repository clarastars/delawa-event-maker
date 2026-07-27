@php
    $copy = [
        'en' => [
            'switch' => 'العربية',
            'invitation_alt' => 'Delawa invitation — 400 SAR purchase voucher',
            'name_label' => 'Enter your name',
            'name_placeholder' => 'Your name',
            'phone_label' => 'Enter your phone number',
            'phone_placeholder' => '5XXXXXXXX',
            'submit' => 'Send verification code',
            'otp_label' => 'Enter verification code',
            'otp_placeholder' => '1234',
            'otp_submit' => 'Verify and show invitation',
            'otp_resend' => 'Resend code',
            'otp_resend_wait' => 'Resend code in :seconds s',
            'otp_cancel' => 'Change details',
            'otp_sent_prefix' => 'We sent a :digits-digit code to',
            'otp_hint' => 'Enter the code sent to your phone to view your invitation.',
            'found' => 'Welcome, :name. Here is your voucher.',
            'found_guest' => 'Here is your voucher.',
            'not_found' => 'We could not find an active voucher for this phone number. Please check your number or contact the store team.',
            'voucher_id' => 'Voucher ID',
            'balance' => 'Balance',
            'expires' => 'Expires',
            'one_time' => 'One-time redemption',
            'yes' => 'Yes',
            'no' => 'No',
            'event_title' => 'Delawa branch opening.',
            'event_date' => 'Sunday, 21 June (21/6/2026).',
            'event_time' => '8:00 PM.',
            'event_branch' => 'Voucher valid at Al Narjis branch only.',
            'event_voucher_note' => 'A special voucher will be provided for on-site coverage via your TikTok account.',
            'event_balance_validity' => 'After activation, voucher balance is available for one month.',
            'debug_otp' => 'Debug mode on: use :code for OTP',
        ],
        'ar' => [
            'switch' => 'English',
            'invitation_alt' => 'دعوة ديلاوة — قسيمة شراء بقيمة 400 ريال',
            'name_label' => 'تسجيل الدعوة بأسم:',
            'name_placeholder' => 'الاسم الكريم',
            'phone_label' => 'أدخل رقم جوالك',
            'phone_placeholder' => '5XXXXXXXX',
            'submit' => 'إرسال رمز التحقق',
            'otp_label' => 'أدخل رمز التحقق',
            'otp_placeholder' => '1234',
            'otp_submit' => 'تحقق واعرض الدعوة',
            'otp_resend' => 'إعادة إرسال الرمز',
            'otp_resend_wait' => 'إعادة الإرسال خلال :seconds ث',
            'otp_cancel' => 'تغيير البيانات',
            'otp_sent_prefix' => 'أرسلنا رمز إلى الرقم:',
            'otp_hint' => 'أدخل الرمز المرسل إلى جوالك لعرض دعوتك.',
            'found' => 'أهلاً :name، هذه قسيمتك.',
            'found_guest' => 'هذه قسيمتك.',
            'not_found' => 'لم نجد قسيمة فعالة لهذا الرقم. يرجى التأكد من رقم الجوال أو التواصل مع فريق المتجر.',
            'voucher_id' => 'رقم القسيمة',
            'balance' => 'الرصيد',
            'expires' => 'تنتهي في',
            'one_time' => 'استخدام لمرة واحدة',
            'yes' => 'نعم',
            'no' => 'لا',
            'event_title' => 'افتتاح فرع ديلاوة.',
            'event_date' => 'الأحد 21 يونيو (21/6/2026).',
            'event_time' => 'الساعة 8 مساءً.',
            'event_branch' => 'القسيمة لفرع النرجس فقط.',
            'event_voucher_note' => 'قسيمة خاصة مقابل التغطية الحضورية عبر حسابكم في التيك توك.',
            'event_balance_validity' => 'بعد التفعيل، رصيد القسيمة متاح لمدة شهر.',
            'debug_otp' => 'وضع التطوير مفعّل: استخدم :code لرمز التحقق',
        ],
    ][$locale];

    $branches = [
        [
            'en' => 'Al Narjis',
            'ar' => 'فرع النرجس',
            'url' => 'https://maps.app.goo.gl/MS1gm2sfdZwW8xBK7',
        ],
    ];

    $dir = $locale === 'ar' ? 'rtl' : 'ltr';
    $nextLocale = $locale === 'ar' ? 'en' : 'ar';
    $otpDigits = (int) config('services.authentica.otp_digits', 4);
    $otpDigitsDisplay = $locale === 'ar'
        ? strtr((string) $otpDigits, '0123456789', '٠١٢٣٤٥٦٧٨٩')
        : (string) $otpDigits;

    $ogTitle = 'ديلاوة - رصيد ٤٠٠ ريال هدية لك';
    $ogDescription = 'سجل الحين في تطبيق ديلاوة وخذ رصيد متجر بـ ٤٠٠ ريال. لا تفوت الفرصة.. هالعرض جايك لأنك تستاهل.';
    $ogImage = asset('images/logo.png');
    $ogUrl = route('accept.index');
@endphp

<!DOCTYPE html>
<html lang="{{ $locale }}" dir="{{ $dir }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Delawa - ديلاوة</title>
        <meta name="description" content="{{ $ogDescription }}">
        <meta property="og:type" content="website">
        <meta property="og:url" content="{{ $ogUrl }}">
        <meta property="og:title" content="{{ $ogTitle }}">
        <meta property="og:description" content="{{ $ogDescription }}">
        <meta property="og:image" content="{{ $ogImage }}">
        <meta property="og:image:secure_url" content="{{ $ogImage }}">
        <meta property="og:image:width" content="1200">
        <meta property="og:image:height" content="1200">
        <meta property="og:image:type" content="image/png">
        <meta property="og:locale" content="ar_SA">
        <meta property="og:site_name" content="Delawa - ديلاوة">
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="{{ $ogTitle }}">
        <meta name="twitter:description" content="{{ $ogDescription }}">
        <meta name="twitter:image" content="{{ $ogImage }}">
        <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon_16x16.png') }}">
        <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon_32x32.png') }}">
        <link rel="icon" type="image/png" sizes="48x48" href="{{ asset('favicon_48x48.png') }}">
        <link rel="icon" type="image/png" sizes="64x64" href="{{ asset('favicon_64x64.png') }}">
        <link rel="icon" type="image/png" sizes="128x128" href="{{ asset('favicon_128x128.png') }}">
        <link rel="icon" type="image/png" sizes="256x256" href="{{ asset('favicon_256x256.png') }}">
        @fonts
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/accept-phone.js', 'resources/js/accept-otp.js'])
        @endif
    </head>
    <body class="min-h-screen bg-[#7D4651] text-slate-950 antialiased">
        <main class="mx-auto flex min-h-screen w-full max-w-6xl flex-col items-center justify-center px-6 py-10">
            <section class="grid w-full gap-3 rounded-[2rem] bg-white/95 p-3 shadow-2xl shadow-slate-900/20 md:grid-cols-[1.1fr_0.9fr]">
                <div class="overflow-hidden rounded-[1.5rem] bg-white">
                    <div class="flex justify-end p-4 pb-0 md:p-5 md:pb-0">
                        <a href="{{ route('accept.index', ['lang' => $nextLocale]) }}" class="rounded-full border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:border-[#7D4651] hover:text-[#4E2E36]">
                            {{ $copy['switch'] }}
                        </a>
                    </div>
                    <img
                        src="{{ asset('images/logo.png') }}"
                        alt="{{ $copy['invitation_alt'] }}"
                        width="600"
                        height="600"
                        class="mx-auto block h-auto w-full max-w-sm rounded-full border-4 border-white p-4 shadow-lg"
                    >
                </div>

                <div class="rounded-[1.5rem] border border-slate-200 bg-slate-50 p-6">
                    @if ($otpDebugMode ?? false)
                        <div class="mb-5 rounded-2xl border border-amber-300 bg-amber-50 p-4 text-sm font-semibold text-amber-900 ring-1 ring-amber-200" dir="ltr">
                            {{ str_replace(':code', $otpDebugCode ?? '1234', $copy['debug_otp']) }}
                        </div>
                    @endif

                    @if (session('status'))
                        <div class="mb-5 rounded-2xl bg-emerald-50 p-4 text-sm font-medium text-emerald-900 ring-1 ring-emerald-200">
                            {{ session('status') }}
                        </div>
                    @endif

                    @if ($step === 'otp')
                        <p class="text-sm leading-6 text-slate-600">{{ $copy['otp_hint'] }}</p>
                        @if ($pending_phone)
                            <p class="mt-3 text-sm leading-7 text-slate-600">
                                {{ str_replace(':digits', $otpDigitsDisplay, $copy['otp_sent_prefix']) }}
                                <bdi dir="ltr" class="ms-1 inline-block rounded-lg bg-slate-100 px-2.5 py-1 font-medium tracking-wide text-slate-950">{{ $pending_phone }}</bdi>
                            </p>
                        @endif

                        <form method="POST" action="{{ route('accept.otp.verify') }}" class="mt-5 space-y-5" data-disable-on-submit>
                            @csrf
                            <input type="hidden" name="lang" value="{{ $locale }}">
                            <div>
                                <label for="otp" class="block text-sm font-semibold text-slate-700">{{ $copy['otp_label'] }}</label>
                                <input id="otp" name="otp" value="{{ old('otp') }}" placeholder="{{ $copy['otp_placeholder'] }}" inputmode="numeric" pattern="[0-9]{{ '{'.$otpDigits.'}' }}" maxlength="{{ $otpDigits }}" dir="ltr" class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-center text-2xl tracking-[0.5em] outline-none transition focus:border-[#7D4651] focus:ring-4 focus:ring-[#7D4651]/20" required autofocus>
                                @error('otp')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                                @if ($otp_error)<p class="mt-2 text-sm text-red-600">{{ $otp_error }}</p>@endif
                            </div>
                            <button type="submit" class="w-full rounded-2xl bg-[#7D4651] px-5 py-3 text-base font-bold text-white shadow-lg shadow-[#7D4651]/25 transition hover:bg-[#6A3A44] disabled:cursor-not-allowed disabled:opacity-60">{{ $copy['otp_submit'] }}</button>
                        </form>
                        <div class="mt-4 flex flex-wrap gap-3">
                            <form method="POST" action="{{ route('accept.otp.resend') }}" data-disable-on-submit>
                                @csrf
                                <input type="hidden" name="lang" value="{{ $locale }}">
                                <button
                                    type="submit"
                                    id="otp-resend-btn"
                                    @disabled(($otp_resend_seconds_remaining ?? 0) > 0)
                                    data-seconds-remaining="{{ $otp_resend_seconds_remaining ?? 0 }}"
                                    data-resend-label="{{ $copy['otp_resend'] }}"
                                    data-resend-wait="{{ $copy['otp_resend_wait'] }}"
                                    data-locale="{{ $locale }}"
                                    class="text-sm font-semibold text-[#4E2E36] underline underline-offset-4 disabled:cursor-not-allowed disabled:text-slate-400 disabled:no-underline"
                                >@if (($otp_resend_seconds_remaining ?? 0) > 0){{ str_replace(':seconds', (string) ($otp_resend_seconds_remaining ?? 0), $copy['otp_resend_wait']) }}@else{{ $copy['otp_resend'] }}@endif</button>
                            </form>
                            <form method="POST" action="{{ route('accept.otp.cancel') }}" data-disable-on-submit>
                                @csrf
                                <input type="hidden" name="lang" value="{{ $locale }}">
                                <button type="submit" class="text-sm font-semibold text-slate-500 underline underline-offset-4 disabled:cursor-not-allowed disabled:opacity-60">{{ $copy['otp_cancel'] }}</button>
                            </form>
                        </div>
                    @else
                    <form
                        id="accept-phone-form"
                        method="POST"
                        action="{{ route('accept.otp.send') }}"
                        class="space-y-5"
                        data-locale="{{ $locale }}"
                        data-disable-on-submit
                    >
                        @csrf
                        <input type="hidden" name="lang" value="{{ $locale }}">

                        <div class="mb-5 space-y-2 rounded-2xl border border-[#7D4651]/20 bg-white p-3.5 text-sm font-semibold leading-6 text-slate-800">
                            <p><span aria-hidden="true">📍</span> {{ $copy['event_title'] }}</p>
                            <p><span aria-hidden="true">🗓</span> {{ $copy['event_date'] }}</p>
                            <p><span aria-hidden="true">⏰</span> {{ $copy['event_time'] }}</p>
                            <p><span aria-hidden="true">🏪</span> {{ $copy['event_branch'] }}</p>
                            <p class="font-medium leading-snug text-slate-700"><span aria-hidden="true">🎁</span> {{ $copy['event_voucher_note'] }}</p>
                            <p class="font-medium leading-snug text-slate-700"><span aria-hidden="true">📅</span> {{ $copy['event_balance_validity'] }}</p>
                        </div>

                        <div>
                            <label for="name" class="block text-sm font-semibold text-slate-700">
                                {{ $copy['name_label'] }}
                            </label>
                            <input
                                id="name"
                                name="name"
                                value="{{ old('name') }}"
                                placeholder="{{ $copy['name_placeholder'] }}"
                                class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-lg outline-none transition focus:border-[#7D4651] focus:ring-4 focus:ring-[#7D4651]/20"
                            >
                            @error('name')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div id="phone-field">
                            <label for="phone" class="block text-sm font-semibold text-slate-700">
                                {{ $copy['phone_label'] }}
                            </label>
                            <div class="mt-2">
                                <input
                                    id="phone"
                                    name="phone"
                                    type="tel"
                                    value="{{ old('phone') }}"
                                    placeholder="{{ $copy['phone_placeholder'] }}"
                                    inputmode="tel"
                                    autocomplete="tel"
                                    dir="ltr"
                                    class="w-full"
                                    required
                                >
                            </div>
                            <p id="phone-client-error" class="mt-2 hidden text-sm text-red-600" role="alert"></p>
                            @error('phone')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <button type="submit" class="w-full cursor-pointer rounded-2xl bg-[#7D4651] px-5 py-3 text-base font-bold text-white shadow-lg shadow-[#7D4651]/25 transition hover:bg-[#6A3A44] disabled:cursor-not-allowed disabled:opacity-60">
                            {{ $copy['submit'] }}
                        </button>
                    </form>
                    @endif

                    @if ($searched)
                        <div class="mt-6 rounded-2xl bg-amber-50 p-4 text-sm font-medium text-amber-900 ring-1 ring-amber-200">
                            {{ $copy['not_found'] }}
                        </div>
                    @endif
                </div>
            </section>

            <section class="mt-10 w-full max-w-6xl text-center text-white">
                <ul class="flex justify-center">
                    @foreach ($branches as $branch)
                        <li>
                            <a
                                href="{{ $branch['url'] }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="group flex flex-col items-center gap-2 rounded-2xl px-1 py-3 transition hover:bg-white/10 sm:gap-3 sm:px-4"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-6 shrink-0 text-white sm:size-8" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M11.54 22.351l.07.04.028.016a.76.76 0 00.723 0l.028-.015.071-.041a16.975 16.975 0 001.144-.742 19.58 19.58 0 002.683-2.282c1.944-1.99 3.963-4.98 3.963-8.827a8.25 8.25 0 00-16.5 0c0 3.846 2.02 6.837 3.963 8.827a19.58 19.58 0 002.682 2.282 16.975 16.975 0 001.145.742zM12 13.5a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd" />
                                </svg>
                                <span class="text-xs leading-snug text-white group-hover:text-white/90 sm:text-base">
                                    <span class="font-semibold">{{ $branch['ar'] }}</span><br>
                                    <span class="font-medium text-white/90">{{ $branch['en'] }}</span>
                                </span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </section>
        </main>
    </body>
</html>
