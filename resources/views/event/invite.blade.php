@php
    $copy = [
        'en' => [
            'switch' => 'العربية',
            'banner_alt' => ':event — invitation',
            'name_label' => 'Enter your name',
            'name_placeholder' => 'Your name',
            'phone_label' => 'Enter your phone number',
            'phone_placeholder' => '5XXXXXXXX',
            'submit' => 'Send verification code',
            'otp_label' => 'Enter verification code',
            'otp_placeholder' => '1234',
            'otp_submit' => 'Verify and show my coupons',
            'otp_resend' => 'Resend code',
            'otp_resend_wait' => 'Resend code in :seconds s',
            'otp_cancel' => 'Change details',
            'otp_sent_prefix' => 'We sent a :digits-digit code to',
            'otp_hint' => 'Enter the code sent to your phone to view your coupons.',
            'not_found' => 'We could not find an active coupon for this phone number. Please check your number or contact the store team.',
            'debug_otp' => 'Debug mode on: use :code for OTP',
        ],
        'ar' => [
            'switch' => 'English',
            'banner_alt' => ':event — دعوة',
            'name_label' => 'تسجيل الدعوة بأسم:',
            'name_placeholder' => 'الاسم الكريم',
            'phone_label' => 'أدخل رقم جوالك',
            'phone_placeholder' => '5XXXXXXXX',
            'submit' => 'إرسال رمز التحقق',
            'otp_label' => 'أدخل رمز التحقق',
            'otp_placeholder' => '1234',
            'otp_submit' => 'تحقق واعرض قسائمي',
            'otp_resend' => 'إعادة إرسال الرمز',
            'otp_resend_wait' => 'إعادة الإرسال خلال :seconds ث',
            'otp_cancel' => 'تغيير البيانات',
            'otp_sent_prefix' => 'أرسلنا رمز إلى الرقم:',
            'otp_hint' => 'أدخل الرمز المرسل إلى جوالك لعرض قسائمك.',
            'not_found' => 'لم نجد قسيمة فعالة لهذا الرقم. يرجى التأكد من رقم الجوال أو التواصل مع فريق المتجر.',
            'debug_otp' => 'وضع التطوير مفعّل: استخدم :code لرمز التحقق',
        ],
    ][$locale];

    $dir = $locale === 'ar' ? 'rtl' : 'ltr';
    $nextLocale = $locale === 'ar' ? 'en' : 'ar';
    $otpDigits = (int) config('services.authentica.otp_digits', 4);
    $otpDigitsDisplay = $locale === 'ar'
        ? strtr((string) $otpDigits, '0123456789', '٠١٢٣٤٥٦٧٨٩')
        : (string) $otpDigits;

    $bannerUrl = $event->bannerUrl();
    $bannerAlt = str_replace(':event', $event->name, $copy['banner_alt']);
    $ogUrl = route('event.invite', $event);
@endphp

<!DOCTYPE html>
<html lang="{{ $locale }}" dir="{{ $dir }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $event->name }} - Delawa - ديلاوة</title>
        <meta name="description" content="{{ $event->name }}">
        <meta property="og:type" content="website">
        <meta property="og:url" content="{{ $ogUrl }}">
        <meta property="og:title" content="{{ $event->name }}">
        <meta property="og:description" content="{{ $event->name }}">
        @if ($bannerUrl)
            <meta property="og:image" content="{{ $bannerUrl }}">
            <meta name="twitter:card" content="summary_large_image">
            <meta name="twitter:image" content="{{ $bannerUrl }}">
        @endif
        <meta property="og:site_name" content="Delawa - ديلاوة">
        <meta name="twitter:title" content="{{ $event->name }}">
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
        <script>
            document.addEventListener('submit', function (e) {
                setTimeout(function () {
                    if (!e.defaultPrevented && e.target.closest('form[data-loading-overlay]')) {
                        const overlay = document.getElementById('loading-overlay');
                        if (overlay) {
                            overlay.classList.remove('hidden');
                            overlay.classList.add('flex');
                        }
                    }
                }, 0);
            });
        </script>
    </head>
    <body class="min-h-screen bg-[#7D4651] text-slate-950 antialiased">
        <div id="loading-overlay" class="fixed inset-0 z-50 hidden items-center justify-center bg-white/70 backdrop-blur-sm transition-opacity duration-300">
            <div class="h-12 w-12 animate-spin rounded-full border-4 border-[#7D4651] border-t-transparent"></div>
        </div>
        <main class="mx-auto flex min-h-screen w-full max-w-6xl flex-col items-center justify-center px-6 py-10">
            <section class="grid w-full gap-3 rounded-[2rem] bg-white/95 p-3 shadow-2xl shadow-slate-900/20 md:grid-cols-[1.1fr_0.9fr]">
                <div class="overflow-hidden rounded-[1.5rem] bg-white">
                    <div class="flex items-center justify-between gap-3 p-4 pb-0 md:p-5 md:pb-0">
                        <h1 class="text-lg font-black text-slate-950">{{ $event->name }}</h1>
                        <a href="{{ route('event.invite', ['event' => $event, 'lang' => $nextLocale]) }}" class="rounded-full border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:border-[#7D4651] hover:text-[#4E2E36]">
                            {{ $copy['switch'] }}
                        </a>
                    </div>
                    @if ($bannerUrl)
                        <img
                            src="{{ $bannerUrl }}"
                            alt="{{ $bannerAlt }}"
                            class="mt-4 block h-auto w-full"
                        >
                    @else
                        <div class="mt-4 flex min-h-48 items-center justify-center bg-[#7D4651]/10 p-10 text-center md:min-h-72">
                            <span class="text-3xl font-black text-[#4E2E36]">{{ $event->name }}</span>
                        </div>
                    @endif
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

                        <form method="POST" action="{{ route('event.otp.verify', $event) }}" class="mt-5 space-y-5" data-disable-on-submit data-loading-overlay>
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
                            <form method="POST" action="{{ route('event.otp.resend', $event) }}" data-disable-on-submit>
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
                            <form method="POST" action="{{ route('event.otp.cancel', $event) }}" data-disable-on-submit>
                                @csrf
                                <input type="hidden" name="lang" value="{{ $locale }}">
                                <button type="submit" class="text-sm font-semibold text-slate-500 underline underline-offset-4 disabled:cursor-not-allowed disabled:opacity-60">{{ $copy['otp_cancel'] }}</button>
                            </form>
                        </div>
                    @else
                    <form
                        id="accept-phone-form"
                        method="POST"
                        action="{{ route('event.otp.send', $event) }}"
                        class="space-y-5"
                        data-locale="{{ $locale }}"
                        data-disable-on-submit
                        data-loading-overlay
                    >
                        @csrf
                        <input type="hidden" name="lang" value="{{ $locale }}">

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
                            
                            @if ($event->maps_link)
                                <div class="mt-4 pt-4 border-t border-slate-200">
                                    <a href="{{ $event->maps_link }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 text-sm font-semibold text-[#7D4651] hover:text-[#4E2E36] hover:underline">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5">
                                            <path fill-rule="evenodd" d="M11.54 22.351l.07.04.028.016a.76.76 0 00.723 0l.028-.015.071-.041a16.975 16.975 0 001.144-.742 19.58 19.58 0 002.683-2.282c1.944-1.99 3.963-4.98 3.963-8.827a8.25 8.25 0 00-16.5 0c0 3.846 2.02 6.837 3.963 8.827a19.58 19.58 0 002.682 2.282 16.975 16.975 0 001.145.742zM12 13.5a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd" />
                                        </svg>
                                        {{ $event->maps_link_label ?: ($locale === 'ar' ? 'اذهب إلى الموقع' : 'Go to location') }}
                                    </a>
                                </div>
                            @endif
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
        </main>
    </body>
</html>
