<?php

namespace App\Http\Controllers;

use App\Contracts\GiftCardBalance;
use App\Contracts\Otp;
use App\Http\Requests\SendAcceptOtpRequest;
use App\Http\Requests\VerifyAcceptOtpRequest;
use App\Models\Contact;
use App\Models\Event;
use App\Models\EventReview;
use App\Models\Product;
use App\Models\Voucher;
use App\Support\PhoneNumber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use RuntimeException;

class EventInviteController extends Controller
{
    private const OTP_RESEND_COOLDOWN_SECONDS = 30;

    public function index(Request $request, Event $event): View
    {
        return $this->view($request, $event, [
            'step' => session()->has($this->pendingKey($event)) ? 'otp' : 'details',
        ]);
    }

    public function sendOtp(SendAcceptOtpRequest $request, Event $event, Otp $otp): RedirectResponse|View
    {
        $contact = $this->findContact(
            PhoneNumber::toE164($request->string('phone')->toString()) ?? ''
        );

        $hasEntries = $contact ? $contact->events()->where('event_id', $event->id)->exists() : false;

        if (! $contact || (! $hasEntries && $this->redeemableVouchers($contact, $event)->isEmpty())) {
            return $this->view($request, $event, [
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
            $this->pendingKey($event) => $this->pendingSessionData($request, $phoneE164, $contact->id),
        ]);

        return redirect()
            ->route('event.invite', ['event' => $event, 'lang' => $this->locale($request)])
            ->with('status', $this->message($request, [
                'en' => 'Verification code sent.',
                'ar' => 'تم إرسال رمز التحقق.',
            ]));
    }

    public function verifyOtp(VerifyAcceptOtpRequest $request, Event $event, Otp $otp): RedirectResponse|View
    {
        $pending = session($this->pendingKey($event));

        if (! is_array($pending) || empty($pending['phone_e164'])) {
            return redirect()
                ->route('event.invite', ['event' => $event, 'lang' => $this->locale($request)])
                ->withErrors(['otp' => $this->message($request, [
                    'en' => 'Your session expired. Please enter your details again.',
                    'ar' => 'انتهت الجلسة. يرجى إدخال بياناتك من جديد.',
                ])]);
        }

        if (! $otp->verify($pending['phone_e164'], $request->string('otp')->toString())) {
            return $this->view($request, $event, [
                'step' => 'otp',
                'otp_error' => $this->message($request, [
                    'en' => 'The verification code is invalid or expired.',
                    'ar' => 'رمز التحقق غير صحيح أو منتهي الصلاحية.',
                ]),
            ]);
        }

        $contact = Contact::query()->find($pending['contact_id'] ?? null);

        session()->forget($this->pendingKey($event));

        if (! $contact) {
            return $this->view($request, $event, [
                'step' => 'details',
                'searched' => true,
            ]);
        }

        $hasEntries = $contact->events()->where('event_id', $event->id)->exists();

        if (! $hasEntries && $this->redeemableVouchers($contact, $event)->isEmpty()) {
            return $this->view($request, $event, [
                'step' => 'details',
                'searched' => true,
            ]);
        }

        session([$this->verifiedKey($event) => $contact->id]);

        return redirect()->route('event.vouchers', ['event' => $event, 'lang' => $this->locale($request)]);
    }

    public function showVouchers(Request $request, Event $event, GiftCardBalance $giftCardBalance): View|RedirectResponse
    {
        $contactId = session($this->verifiedKey($event));

        $contact = filled($contactId) ? Contact::query()->find((int) $contactId) : null;

        $vouchers = $contact ? $this->redeemableVouchers($contact, $event) : collect();
        $hasEntries = $contact ? $contact->events()->where('event_id', $event->id)->exists() : false;

        if (! $contact || (! $hasEntries && $vouchers->isEmpty())) {
            session()->forget($this->verifiedKey($event));

            return redirect()->route('event.invite', ['event' => $event, 'lang' => $this->locale($request)]);
        }

        $contact->markAsActivated();

        $entriesAllowed = 0;
        $pivot = $contact->events()->where('event_id', $event->id)->first()?->pivot;
        if ($pivot) {
            $entriesAllowed = $pivot->entries;
        }

        $remainingEntries = max(0, $entriesAllowed - $vouchers->count());

        $products = collect();
        if ($remainingEntries > 0) {
            $products = $event->products;
        }

        return view('event.vouchers', [
            'locale' => $this->locale($request),
            'event' => $event,
            'contact' => $contact,
            'vouchers' => $vouchers,
            'remainingEntries' => $remainingEntries,
            'products' => $products,
            'remainingBalances' => $vouchers->mapWithKeys(fn ($voucher) => [
                $voucher->id => $giftCardBalance->remainingBalance($voucher->voucher_id),
            ]),
        ]);
    }

    public function claimProduct(Request $request, Event $event, Product $product): RedirectResponse
    {
        $contactId = session($this->verifiedKey($event));
        $contact = filled($contactId) ? Contact::query()->find((int) $contactId) : null;

        if (! $contact) {
            session()->forget($this->verifiedKey($event));

            return redirect()->route('event.invite', ['event' => $event, 'lang' => $this->locale($request)]);
        }

        $vouchers = $this->redeemableVouchers($contact, $event);

        $entriesAllowed = 0;
        $pivot = $contact->events()->where('event_id', $event->id)->first()?->pivot;
        if ($pivot) {
            $entriesAllowed = $pivot->entries;
        }

        $remainingEntries = max(0, $entriesAllowed - $vouchers->count());

        if ($remainingEntries <= 0) {
            return redirect()->route('event.vouchers', ['event' => $event, 'lang' => $this->locale($request)])
                ->withErrors(['product' => $this->message($request, [
                    'en' => 'You have already claimed all your allowed vouchers.',
                    'ar' => 'لقد قمت بالمطالبة بجميع قسائمك المسموح بها.',
                ])]);
        }

        if ($product->event_id !== $event->id) {
            return redirect()->route('event.vouchers', ['event' => $event, 'lang' => $this->locale($request)])
                ->withErrors(['product' => $this->message($request, [
                    'en' => 'Invalid product selected.',
                    'ar' => 'المنتج المحدد غير صالح.',
                ])]);
        }

        $voucher = DB::transaction(function () use ($contact, $product) {
            $voucher = Voucher::query()
                ->whereNull('contact_id')
                ->where('status', Voucher::STATUS_ACTIVE)
                ->where('product_id', $product->id)
                ->orderBy('id')
                ->lockForUpdate()
                ->first();

            if ($voucher) {
                $voucher->update(['contact_id' => $contact->id]);
            }

            return $voucher;
        });

        if (! $voucher) {
            return redirect()->route('event.vouchers', ['event' => $event, 'lang' => $this->locale($request)])
                ->withErrors(['product' => $this->message($request, [
                    'en' => 'No vouchers available for this product.',
                    'ar' => 'لا توجد قسائم متاحة لهذا المنتج.',
                ])]);
        }

        return redirect()->route('event.vouchers', ['event' => $event, 'lang' => $this->locale($request)]);
    }

    public function submitReview(Request $request, Event $event): RedirectResponse
    {
        $contactId = session($this->verifiedKey($event));
        $contact = filled($contactId) ? Contact::query()->find((int) $contactId) : null;

        if (! $contact) {
            session()->forget($this->verifiedKey($event));

            return redirect()->route('event.invite', ['event' => $event, 'lang' => $this->locale($request)]);
        }

        $request->validate([
            'experience' => ['required', 'string', 'max:1000'],
        ]);

        EventReview::updateOrCreate(
            ['event_id' => $event->id, 'contact_id' => $contact->id],
            ['experience' => $request->string('experience')->toString()]
        );

        return redirect()->route('event.vouchers', ['event' => $event, 'lang' => $this->locale($request)])
            ->with('status', $this->message($request, [
                'en' => 'Thank you for your feedback!',
                'ar' => 'شكراً لملاحظاتك!',
            ]));
    }

    public function resendOtp(Request $request, Event $event, Otp $otp): RedirectResponse
    {
        $pending = session($this->pendingKey($event));

        if (! is_array($pending) || empty($pending['phone_e164'])) {
            return redirect()
                ->route('event.invite', ['event' => $event, 'lang' => $this->locale($request)])
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
        session([$this->pendingKey($event) => $pending]);

        return back()->with('status', $this->message($request, [
            'en' => 'Verification code sent again.',
            'ar' => 'تم إرسال الرمز مرة أخرى.',
        ]));
    }

    public function cancelOtp(Request $request, Event $event): RedirectResponse
    {
        session()->forget($this->pendingKey($event));

        return redirect()->route('event.invite', ['event' => $event, 'lang' => $this->locale($request)]);
    }

    private function pendingKey(Event $event): string
    {
        return "event_invite.{$event->id}.pending";
    }

    private function verifiedKey(Event $event): string
    {
        return "event_invite.{$event->id}.verified_contact_id";
    }

    private function findContact(string $phoneE164): ?Contact
    {
        return Contact::query()
            ->where('phone_normalized', Contact::normalizePhone($phoneE164))
            ->first();
    }

    /**
     * @return Collection<int, Voucher>
     */
    private function redeemableVouchers(Contact $contact, Event $event): Collection
    {
        return $contact->vouchers()
            ->where('event_id', $event->id)
            ->redeemable()
            ->orderBy('id')
            ->get();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function view(Request $request, Event $event, array $data = []): View
    {
        $pending = session($this->pendingKey($event));

        return view('event.invite', array_merge([
            'locale' => $data['locale'] ?? $pending['lang'] ?? $this->locale($request),
            'event' => $event,
            'step' => 'details',
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
