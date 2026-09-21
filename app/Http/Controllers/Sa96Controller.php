<?php

namespace App\Http\Controllers;

use App\Actions\RegisterSa96Visitor;
use App\Actions\WithdrawSa96Visitor;
use App\Contracts\Otp;
use App\Http\Requests\StoreSa96RegistrationRequest;
use App\Http\Requests\VerifySa96OtpRequest;
use App\Http\Requests\WithdrawSa96RegistrationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class Sa96Controller extends Controller
{
    private const PENDING_KEY = 'sa96.pending';

    private const OTP_RESEND_COOLDOWN_SECONDS = 30;

    public function create(Request $request): View
    {
        return $this->formView($request);
    }

    public function store(StoreSa96RegistrationRequest $request, Otp $otp): RedirectResponse
    {
        if (filled($request->input('website'))) {
            return redirect()
                ->route('sa96.thanks', ['lang' => $request->locale()])
                ->with('sa96_registered', true);
        }

        $phoneE164 = $request->phoneE164();

        try {
            $otp->send($phoneE164);
        } catch (RuntimeException) {
            return back()
                ->withInput()
                ->withErrors(['phone' => $this->message($request->locale(), [
                    'en' => 'We could not send a verification code. Please try again shortly.',
                    'ar' => 'تعذّر إرسال رمز التحقق. يرجى المحاولة بعد قليل.',
                ])]);
        }

        session([
            self::PENDING_KEY => [
                'name' => $request->validated('name'),
                'birth_year' => (int) $request->validated('birth_year'),
                'phone_e164' => $phoneE164,
                'locale' => $request->locale(),
                'ip_address' => $request->ip(),
                'user_agent' => mb_substr((string) $request->userAgent(), 0, 512),
                'otp_sent_at' => now()->timestamp,
            ],
        ]);

        return redirect()
            ->route('sa96.create', ['lang' => $request->locale()])
            ->with('status', $this->message($request->locale(), [
                'en' => 'Verification code sent.',
                'ar' => 'تم إرسال رمز التحقق.',
            ]));
    }

    public function verifyOtp(VerifySa96OtpRequest $request, Otp $otp, RegisterSa96Visitor $register): RedirectResponse
    {
        $pending = session(self::PENDING_KEY);

        if (! is_array($pending) || empty($pending['phone_e164'])) {
            return redirect()
                ->route('sa96.create', ['lang' => $request->locale()])
                ->withErrors(['otp' => $this->message($request->locale(), [
                    'en' => 'Your session expired. Please enter your details again.',
                    'ar' => 'انتهت الجلسة. يرجى إدخال بياناتك من جديد.',
                ])]);
        }

        if (! $otp->verify($pending['phone_e164'], $request->string('otp')->toString())) {
            return redirect()
                ->route('sa96.create', ['lang' => $pending['locale'] ?? $request->locale()])
                ->withErrors(['otp' => $this->message($request->locale(), [
                    'en' => 'The verification code is invalid or expired.',
                    'ar' => 'رمز التحقق غير صحيح أو منتهي الصلاحية.',
                ])]);
        }

        $register->execute([
            'name' => (string) $pending['name'],
            'birth_year' => (int) $pending['birth_year'],
            'phone_e164' => (string) $pending['phone_e164'],
            'locale' => (string) ($pending['locale'] ?? 'ar'),
            'ip_address' => $pending['ip_address'] ?? null,
            'user_agent' => $pending['user_agent'] ?? null,
        ]);

        session()->forget(self::PENDING_KEY);

        return redirect()
            ->route('sa96.thanks', ['lang' => $pending['locale'] ?? $request->locale()])
            ->with('sa96_registered', true);
    }

    public function resendOtp(Request $request, Otp $otp): RedirectResponse
    {
        $pending = session(self::PENDING_KEY);
        $locale = $this->locale($request);

        if (! is_array($pending) || empty($pending['phone_e164'])) {
            return redirect()
                ->route('sa96.create', ['lang' => $locale])
                ->withErrors(['otp' => $this->message($locale, [
                    'en' => 'Your session expired. Please enter your details again.',
                    'ar' => 'انتهت الجلسة. يرجى إدخال بياناتك من جديد.',
                ])]);
        }

        $secondsUntilResend = $this->secondsUntilOtpResendAllowed($pending);

        if ($secondsUntilResend > 0) {
            return back()->withErrors(['otp' => $this->resendCooldownMessage($locale, $secondsUntilResend)]);
        }

        try {
            $otp->send($pending['phone_e164']);
        } catch (RuntimeException) {
            return back()->withErrors(['otp' => $this->message($locale, [
                'en' => 'We could not resend the verification code. Please try again shortly.',
                'ar' => 'تعذّر إعادة إرسال رمز التحقق. يرجى المحاولة بعد قليل.',
            ])]);
        }

        $pending['otp_sent_at'] = now()->timestamp;
        session([self::PENDING_KEY => $pending]);

        return back()->with('status', $this->message($locale, [
            'en' => 'Verification code sent again.',
            'ar' => 'تم إرسال الرمز مرة أخرى.',
        ]));
    }

    public function cancelOtp(Request $request): RedirectResponse
    {
        session()->forget(self::PENDING_KEY);

        return redirect()->route('sa96.create', ['lang' => $this->locale($request)]);
    }

    public function thanks(Request $request): View|RedirectResponse
    {
        if (! session('sa96_registered')) {
            return redirect()->route('sa96.create', ['lang' => $this->locale($request)]);
        }

        return view('sa96.thanks', $this->viewData($request));
    }

    public function privacy(Request $request): View
    {
        return view('sa96.privacy', $this->viewData($request));
    }

    public function withdraw(Request $request): View
    {
        return view('sa96.withdraw', $this->viewData($request));
    }

    public function destroy(WithdrawSa96RegistrationRequest $request, WithdrawSa96Visitor $withdraw): RedirectResponse
    {
        $withdraw->execute($request->phoneE164());

        return redirect()
            ->route('sa96.withdraw', ['lang' => $request->locale()])
            ->with('status', $request->locale() === 'en'
                ? 'If this number was registered, consent has been withdrawn and personal data has been deleted.'
                : 'إذا كان هذا الرقم مسجّلاً، فقد تم سحب الموافقة وحذف البيانات الشخصية.');
    }

    private function formView(Request $request, array $extra = []): View
    {
        $pending = session(self::PENDING_KEY);
        $hasPending = is_array($pending) && filled($pending['phone_e164'] ?? null);
        $locale = $extra['locale'] ?? ($hasPending ? ($pending['locale'] ?? null) : null) ?? $this->locale($request);

        return view('sa96.create', array_merge($this->viewData($request, $locale), [
            'step' => $hasPending ? 'otp' : 'details',
            'pending_phone' => $hasPending ? $pending['phone_e164'] : null,
            'otp_resend_seconds_remaining' => $hasPending ? $this->secondsUntilOtpResendAllowed($pending) : 0,
            'otpDebugMode' => (bool) config('services.authentica.debug_otp'),
            'otpDebugCode' => (string) config('services.authentica.debug_otp_code', '1234'),
            'otpDigits' => (int) config('services.authentica.otp_digits', 4),
        ], $extra));
    }

    /**
     * @return array<string, mixed>
     */
    private function viewData(Request $request, ?string $locale = null): array
    {
        $locale ??= $this->locale($request);

        return [
            'locale' => $locale,
            'dir' => $locale === 'ar' ? 'rtl' : 'ltr',
            'nextLocale' => $locale === 'ar' ? 'en' : 'ar',
            'consents' => config('sa96.consents.'.$locale),
            'controllerName' => config('sa96.controller_name'),
            'controllerLegalName' => config('sa96.controller_legal_name'),
            'privacyEmail' => config('sa96.privacy_email'),
            'noticeVersion' => config('sa96.notice_version'),
            'retentionDays' => (int) config('sa96.retention_days'),
            'minAge' => (int) config('sa96.min_age'),
            'sdaiaUrl' => config('sa96.sdaia_complaints_url'),
        ];
    }

    /**
     * @param  array<string, mixed>  $pending
     */
    private function secondsUntilOtpResendAllowed(array $pending): int
    {
        if (empty($pending['otp_sent_at'])) {
            return 0;
        }

        $elapsed = now()->timestamp - (int) $pending['otp_sent_at'];

        return max(0, self::OTP_RESEND_COOLDOWN_SECONDS - $elapsed);
    }

    private function resendCooldownMessage(string $locale, int $seconds): string
    {
        return str_replace(':seconds', (string) $seconds, $this->message($locale, [
            'en' => 'Please wait :seconds seconds before requesting a new code.',
            'ar' => 'يرجى الانتظار :seconds ثانية قبل طلب رمز جديد.',
        ]));
    }

    /**
     * @param  array{en: string, ar: string}  $messages
     */
    private function message(string $locale, array $messages): string
    {
        return $messages[$locale] ?? $messages['ar'];
    }

    private function locale(Request $request): string
    {
        return $request->query('lang') === 'en' || $request->input('lang') === 'en' ? 'en' : 'ar';
    }
}
