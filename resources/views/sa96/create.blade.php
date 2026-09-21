@php
    $step = $step ?? 'details';
    $otpDigits = $otpDigits ?? (int) config('services.authentica.otp_digits', 4);
    $otpDigitsDisplay = $locale === 'ar'
        ? strtr((string) $otpDigits, '0123456789', '٠١٢٣٤٥٦٧٨٩')
        : (string) $otpDigits;

    $copy = [
        'en' => [
            'kicker' => 'Saudi National Day 96',
            'title' => 'Come celebrate National Day with us',
            'birth_year' => 'Year of birth',
            'birth_placeholder' => '1990',
            'name' => 'Your name',
            'name_placeholder' => 'Your name',
            'phone' => 'Mobile number',
            'phone_placeholder' => '5XXXXXXXX',
            'submit' => 'Send verification code',
            'otp_label' => 'Verification code',
            'otp_placeholder' => '1234',
            'otp_submit' => 'Verify and join',
            'otp_hint' => 'Enter the code we sent to your mobile.',
            'otp_sent_prefix' => 'We sent a :digits-digit code to',
            'otp_resend' => 'Resend code',
            'otp_resend_wait' => 'Resend code in :seconds s',
            'otp_cancel' => 'Change details',
            'debug_otp' => 'Debug mode on: use :code for OTP',
            'privacy' => 'Privacy Notice',
            'withdraw' => 'Withdraw consent',
        ],
        'ar' => [
            'kicker' => 'اليوم الوطني السعودي 96',
            'title' => 'يلا نحتفل باليوم الوطني',
            'birth_year' => 'سنة الميلاد',
            'birth_placeholder' => '1990',
            'name' => 'اسمك',
            'name_placeholder' => 'الاسم الكريم',
            'phone' => 'رقم الجوال',
            'phone_placeholder' => '5XXXXXXXX',
            'submit' => 'إرسال رمز التحقق',
            'otp_label' => 'رمز التحقق',
            'otp_placeholder' => '1234',
            'otp_submit' => 'تحقق وانضم',
            'otp_hint' => 'أدخل الرمز اللي أرسلناه على جوالك.',
            'otp_sent_prefix' => 'أرسلنا رمز بـ :digits خانات إلى',
            'otp_resend' => 'إعادة إرسال الرمز',
            'otp_resend_wait' => 'إعادة الإرسال خلال :seconds ث',
            'otp_cancel' => 'تغيير البيانات',
            'debug_otp' => 'وضع التطوير مفعّل: استخدم :code لرمز التحقق',
            'privacy' => 'إشعار الخصوصية',
            'withdraw' => 'سحب الموافقة',
        ],
    ][$locale];

    $inputClass = 'min-h-12 w-full rounded-2xl border border-slate-200 bg-white px-4 text-base text-slate-950 shadow-sm outline-none focus:border-[#006C35] focus:ring-4 focus:ring-[#006C35]/20';
    $errorClass = 'mt-2 text-sm font-medium text-red-700';
@endphp

<x-sa96-layout
    :locale="$locale"
    :dir="$dir"
    :next-locale="$nextLocale"
    :title="$copy['kicker'].' — Delawa'"
    :description="$copy['title']"
>
    <main class="flex flex-1 flex-col">
        <section class="rounded-[1.75rem] bg-white/95 p-5 shadow-2xl shadow-black/20">
            <p class="text-sm font-semibold tracking-wide text-[#006C35]">{{ $copy['kicker'] }}</p>
            <h1 class="mt-1 text-2xl leading-tight text-slate-950">{{ $copy['title'] }}</h1>

            @if ($otpDebugMode ?? false)
                <div class="mt-4 rounded-2xl border border-amber-300 bg-amber-50 p-3 text-sm font-semibold text-amber-900 ring-1 ring-amber-200" dir="ltr">
                    {{ str_replace(':code', $otpDebugCode ?? '1234', $copy['debug_otp']) }}
                </div>
            @endif

            @if (session('status'))
                <div class="mt-4 rounded-2xl bg-emerald-50 p-3 text-sm font-medium text-emerald-900 ring-1 ring-emerald-200">
                    {{ session('status') }}
                </div>
            @endif

            @if ($step === 'otp')
                <div class="mt-6 flex flex-col gap-5">
                    <p class="text-sm leading-6 text-slate-600">{{ $copy['otp_hint'] }}</p>
                    @if ($pending_phone ?? null)
                        <p class="text-sm leading-7 text-slate-600">
                            {{ str_replace(':digits', $otpDigitsDisplay, $copy['otp_sent_prefix']) }}
                            <bdi dir="ltr" class="ms-1 inline-block rounded-lg bg-slate-100 px-2.5 py-1 font-medium tracking-wide text-slate-950">{{ $pending_phone }}</bdi>
                        </p>
                    @endif

                    <form method="POST" action="{{ route('sa96.otp.verify') }}" class="flex flex-col gap-5" data-disable-on-submit>
                        @csrf
                        <input type="hidden" name="lang" value="{{ $locale }}">
                        <div>
                            <label for="otp" class="mb-2 block text-sm font-semibold text-slate-800">{{ $copy['otp_label'] }}</label>
                            <input
                                id="otp"
                                name="otp"
                                value="{{ old('otp') }}"
                                placeholder="{{ $copy['otp_placeholder'] }}"
                                inputmode="numeric"
                                pattern="[0-9]{{ '{'.$otpDigits.'}' }}"
                                maxlength="{{ $otpDigits }}"
                                autocomplete="one-time-code"
                                enterkeyhint="done"
                                dir="ltr"
                                autofocus
                                class="min-h-14 w-full rounded-2xl border border-slate-200 bg-white px-4 text-center text-2xl tracking-[0.4em] text-slate-950 shadow-sm outline-none focus:border-[#006C35] focus:ring-4 focus:ring-[#006C35]/20"
                                required
                            >
                            @error('otp')
                                <p class="{{ $errorClass }}" role="alert">{{ $message }}</p>
                            @enderror
                        </div>
                        <button
                            type="submit"
                            class="min-h-12 w-full rounded-2xl bg-[#006C35] px-4 text-base font-semibold text-white shadow-lg shadow-[#006C35]/30 disabled:cursor-not-allowed disabled:opacity-60"
                        >
                            {{ $copy['otp_submit'] }}
                        </button>
                    </form>

                    <div class="flex flex-wrap gap-4">
                        <form method="POST" action="{{ route('sa96.otp.resend') }}" data-disable-on-submit>
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
                                class="text-sm font-semibold text-[#006C35] underline underline-offset-4 disabled:cursor-not-allowed disabled:text-slate-400 disabled:no-underline"
                            >
                                @if (($otp_resend_seconds_remaining ?? 0) > 0)
                                    {{ str_replace(':seconds', (string) ($otp_resend_seconds_remaining ?? 0), $copy['otp_resend_wait']) }}
                                @else
                                    {{ $copy['otp_resend'] }}
                                @endif
                            </button>
                        </form>
                        <form method="POST" action="{{ route('sa96.otp.cancel') }}" data-disable-on-submit>
                            @csrf
                            <input type="hidden" name="lang" value="{{ $locale }}">
                            <button type="submit" class="text-sm font-semibold text-slate-500 underline underline-offset-4">
                                {{ $copy['otp_cancel'] }}
                            </button>
                        </form>
                    </div>
                </div>
            @else
                <form
                    id="sa96-form"
                    method="POST"
                    action="{{ route('sa96.store') }}"
                    class="mt-6 flex flex-col gap-5"
                    data-locale="{{ $locale }}"
                    data-disable-on-submit
                    novalidate
                >
                    @csrf
                    <input type="hidden" name="lang" value="{{ $locale }}">
                    <div class="absolute -left-[9999px] h-0 w-0 overflow-hidden" aria-hidden="true">
                        <label for="website">Company</label>
                        <input id="website" type="text" name="website" value="" tabindex="-1" autocomplete="off">
                    </div>

                    <div>
                        <label for="birth_year" class="mb-2 block text-sm font-semibold text-slate-800">{{ $copy['birth_year'] }}</label>
                        <input
                            id="birth_year"
                            name="birth_year"
                            type="text"
                            inputmode="numeric"
                            pattern="[0-9]*"
                            maxlength="4"
                            autocomplete="bday-year"
                            enterkeyhint="next"
                            value="{{ old('birth_year') }}"
                            placeholder="{{ $copy['birth_placeholder'] }}"
                            class="{{ $inputClass }}"
                        >
                        @error('birth_year')
                            <p class="{{ $errorClass }}" role="alert">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="name" class="mb-2 block text-sm font-semibold text-slate-800">{{ $copy['name'] }}</label>
                        <input
                            id="name"
                            name="name"
                            type="text"
                            autocomplete="name"
                            autocapitalize="words"
                            enterkeyhint="next"
                            value="{{ old('name') }}"
                            placeholder="{{ $copy['name_placeholder'] }}"
                            class="{{ $inputClass }}"
                        >
                        @error('name')
                            <p class="{{ $errorClass }}" role="alert">{{ $message }}</p>
                        @enderror
                    </div>

                    <div id="phone-field">
                        <label for="phone" class="mb-2 block text-sm font-semibold text-slate-800">{{ $copy['phone'] }}</label>
                        <input
                            id="phone"
                            name="phone"
                            type="tel"
                            inputmode="tel"
                            autocomplete="tel"
                            enterkeyhint="done"
                            value="{{ old('phone') }}"
                            placeholder="{{ $copy['phone_placeholder'] }}"
                            class="{{ $inputClass }}"
                        >
                        <p id="phone-client-error" class="{{ $errorClass }} hidden" role="alert"></p>
                        @error('phone')
                            <p class="{{ $errorClass }}" role="alert">{{ $message }}</p>
                        @enderror
                    </div>

                    <label class="flex min-h-12 cursor-pointer items-start gap-3 rounded-2xl bg-[#006C35]/10 p-4 ring-1 ring-[#006C35]/20">
                        <input
                            type="checkbox"
                            name="consent"
                            value="1"
                            class="mt-1 size-6 shrink-0 rounded-md border-slate-300 text-[#006C35] accent-[#006C35]"
                            @checked(old('consent'))
                        >
                        <span class="text-sm leading-6 text-slate-800">
                            {{ $consents['agree'] }}
                            <a href="{{ route('sa96.privacy', ['lang' => $locale]) }}" class="font-semibold text-[#006C35] underline underline-offset-2">{{ $copy['privacy'] }}</a>
                        </span>
                    </label>
                    @error('consent')
                        <p class="{{ $errorClass }}" role="alert">{{ $message }}</p>
                    @enderror

                    <button
                        type="submit"
                        class="min-h-12 w-full rounded-2xl bg-[#006C35] px-4 text-base font-semibold text-white shadow-lg shadow-[#006C35]/30 disabled:cursor-not-allowed disabled:opacity-60"
                    >
                        {{ $copy['submit'] }}
                    </button>
                </form>
            @endif

            <p class="mt-5 text-center text-sm text-slate-500">
                <a href="{{ route('sa96.privacy', ['lang' => $locale]) }}" class="underline underline-offset-2">{{ $copy['privacy'] }}</a>
                ·
                <a href="{{ route('sa96.withdraw', ['lang' => $locale]) }}" class="underline underline-offset-2">{{ $copy['withdraw'] }}</a>
            </p>
        </section>
    </main>
</x-sa96-layout>
