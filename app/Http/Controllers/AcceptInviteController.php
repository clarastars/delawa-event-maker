<?php

namespace App\Http\Controllers;

use App\Contracts\GiftCardBalance;
use App\Contracts\Otp;
use App\Http\Requests\SendAcceptOtpRequest;
use App\Http\Requests\VerifyAcceptOtpRequest;
use App\Models\Contact;
use App\Models\Voucher;
use App\Services\ContactImporter;
use App\Support\PhoneNumber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class AcceptInviteController extends Controller
{
    private const SESSION_PENDING = 'accept.pending';

    private const SESSION_VERIFIED_VOUCHER = 'accept.verified_voucher_id';

    private const OTP_RESEND_COOLDOWN_SECONDS = 30;

    public function __construct(private ContactImporter $contactImporter) {}

    public function index(Request $request): View
    {
        return $this->view($request, [
            'step' => session()->has(self::SESSION_PENDING) ? 'otp' : 'details',
        ]);
    }

    public function sendOtp(SendAcceptOtpRequest $request, Otp $otp): RedirectResponse|View
    {
        $contact = $this->findContact(
            PhoneNumber::toE164($request->string('phone')->toString()) ?? ''
        );

        if (! $contact || ! $this->resolveRedeemableVoucher($contact, assignIfMissing: true)) {
            return $this->view($request, [
                'step' => 'details',
                'searched' => true,
            ]);
        }

        $phoneE164 = $request->phoneE164();

        try {
            $otp->send($phoneE164);
        } catch (RuntimeException) {
            return back()
                ->withInput()
                ->withErrors(['phone' => $this->message($request, [
                    'en' => 'We could not send a verification code. Please try again shortly.',
                    'ar' => 'تعذّر إرسال رمز التحقق. يرجى المحاولة بعد قليل.',
                ])]);
        }

        session([
            self::SESSION_PENDING => $this->pendingSessionData(
                $request,
                $phoneE164,
                $contact->id,
            ),
        ]);

        return redirect()
            ->route('accept.index', ['lang' => $this->locale($request)])
            ->with('status', $this->message($request, [
                'en' => 'Verification code sent.',
                'ar' => 'تم إرسال رمز التحقق.',
            ]));
    }

    public function verifyOtp(VerifyAcceptOtpRequest $request, Otp $otp): RedirectResponse|View
    {
        $pending = session(self::SESSION_PENDING);

        if (! is_array($pending) || empty($pending['phone_e164'])) {
            return redirect()
                ->route('accept.index', ['lang' => $this->locale($request)])
                ->withErrors(['otp' => $this->message($request, [
                    'en' => 'Your session expired. Please enter your details again.',
                    'ar' => 'انتهت الجلسة. يرجى إدخال بياناتك من جديد.',
                ])]);
        }

        if (! $otp->verify($pending['phone_e164'], $request->string('otp')->toString())) {
            return $this->view($request, [
                'step' => 'otp',
                'otp_error' => $this->message($request, [
                    'en' => 'The verification code is invalid or expired.',
                    'ar' => 'رمز التحقق غير صحيح أو منتهي الصلاحية.',
                ]),
            ]);
        }

        $contact = Contact::query()->find($pending['contact_id'] ?? null);

        $voucher = $contact ? $this->resolveRedeemableVoucher($contact) : null;

        session()->forget(self::SESSION_PENDING);

        if (! $voucher) {
            return $this->view($request, [
                'step' => 'details',
                'searched' => true,
            ]);
        }

        session([self::SESSION_VERIFIED_VOUCHER => $voucher->id]);

        return redirect()->route('accept.voucher.show', ['lang' => $this->locale($request)]);
    }

    public function showVoucher(Request $request, GiftCardBalance $giftCardBalance): View|RedirectResponse
    {
        $voucherId = session(self::SESSION_VERIFIED_VOUCHER);

        if (! filled($voucherId)) {
            return redirect()->route('accept.index', ['lang' => $this->locale($request)]);
        }

        $voucher = Voucher::query()->find((int) $voucherId);

        if (! $voucher?->redeemable()) {
            session()->forget(self::SESSION_VERIFIED_VOUCHER);

            return redirect()->route('accept.index', ['lang' => $this->locale($request)]);
        }

        $voucher->contact?->markAsActivated();

        return view('accept.voucher', [
            'locale' => $this->locale($request),
            'voucher' => $voucher,
            'remainingBalance' => $voucher->isLocal()
                ? $voucher->storedRemainingBalance()
                : $giftCardBalance->remainingBalance($voucher->voucher_id),
        ]);
    }

    public function resendOtp(Request $request, Otp $otp): RedirectResponse
    {
        $pending = session(self::SESSION_PENDING);

        if (! is_array($pending) || empty($pending['phone_e164'])) {
            return redirect()
                ->route('accept.index', ['lang' => $this->locale($request)])
                ->withErrors(['otp' => $this->message($request, [
                    'en' => 'Your session expired. Please enter your details again.',
                    'ar' => 'انتهت الجلسة. يرجى إدخال بياناتك من جديد.',
                ])]);
        }

        $secondsUntilResend = $this->secondsUntilOtpResendAllowed($pending);

        if ($secondsUntilResend > 0) {
            return back()->withErrors(['otp' => $this->resendCooldownMessage($request, $secondsUntilResend)]);
        }

        try {
            $otp->send($pending['phone_e164']);
        } catch (RuntimeException) {
            return back()->withErrors(['otp' => $this->message($request, [
                'en' => 'We could not resend the verification code. Please try again shortly.',
                'ar' => 'تعذّر إعادة إرسال رمز التحقق. يرجى المحاولة بعد قليل.',
            ])]);
        }

        $pending['otp_sent_at'] = now()->timestamp;
        session([self::SESSION_PENDING => $pending]);

        return back()->with('status', $this->message($request, [
            'en' => 'Verification code sent again.',
            'ar' => 'تم إرسال الرمز مرة أخرى.',
        ]));
    }

    public function cancelOtp(Request $request): RedirectResponse
    {
        session()->forget(self::SESSION_PENDING);

        return redirect()->route('accept.index', ['lang' => $this->locale($request)]);
    }

    private function findContact(string $phoneE164): ?Contact
    {
        return Contact::query()
            ->where('phone_normalized', Contact::normalizePhone($phoneE164))
            ->first();
    }

    private function resolveRedeemableVoucher(Contact $contact, bool $assignIfMissing = false): ?Voucher
    {
        $voucher = $contact->voucher()->redeemable()->first();

        if ($voucher) {
            return $voucher;
        }

        if (! $assignIfMissing) {
            return null;
        }

        $this->contactImporter->assignVoucher($contact);

        return $contact->fresh()->voucher()->redeemable()->first();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function view(Request $request, array $data = []): View
    {
        $pending = session(self::SESSION_PENDING);

        return view('accept', array_merge([
            'locale' => $data['locale'] ?? $pending['lang'] ?? $this->locale($request),
            'step' => 'details',
            'contact' => null,
            'voucher' => null,
            'searched' => false,
            'otp_error' => null,
            'pending_phone' => is_array($pending) ? ($pending['phone_e164'] ?? null) : null,
            'otp_resend_seconds_remaining' => is_array($pending)
                ? $this->secondsUntilOtpResendAllowed($pending)
                : 0,
            'otpDebugMode' => config('services.authentica.debug_otp'),
            'otpDebugCode' => config('services.authentica.debug_otp_code', '1234'),
        ], $data));
    }

    /**
     * @return array{name: ?string, phone_e164: string, phone_normalized: string, contact_id: int, lang: string, otp_sent_at: int}
     */
    private function pendingSessionData(SendAcceptOtpRequest $request, string $phoneE164, int $contactId): array
    {
        return [
            'name' => filled($request->input('name')) ? trim($request->string('name')->toString()) : null,
            'phone_e164' => $phoneE164,
            'phone_normalized' => Contact::normalizePhone($phoneE164),
            'contact_id' => $contactId,
            'lang' => $this->locale($request),
            'otp_sent_at' => now()->timestamp,
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

    private function resendCooldownMessage(Request $request, int $seconds): string
    {
        $messages = [
            'en' => 'Please wait :seconds seconds before requesting a new code.',
            'ar' => 'يرجى الانتظار :seconds ثانية قبل طلب رمز جديد.',
        ];

        return str_replace(':seconds', (string) $seconds, $messages[$this->locale($request)]);
    }

    private function locale(Request $request): string
    {
        return $request->query('lang') === 'en' || $request->input('lang') === 'en' ? 'en' : 'ar';
    }

    /**
     * @param  array{en: string, ar: string}  $messages
     */
    private function message(Request $request, array $messages): string
    {
        return $messages[$this->locale($request)];
    }
}
