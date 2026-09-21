<?php

use App\Contracts\Otp;
use App\Models\Sa96Registration;
use App\Support\Sa96Privacy;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function validSa96Payload(array $overrides = []): array
{
    return array_merge([
        'name' => 'سارة أحمد',
        'birth_year' => '1995',
        'phone' => '0551234567',
        'consent' => '1',
        'lang' => 'ar',
    ], $overrides);
}

function mockSa96OtpSend(?string $phone = '+966551234567'): void
{
    test()->mock(Otp::class, function ($mock) use ($phone): void {
        $mock->shouldReceive('send')->once()->with($phone);
    });
}

test('guest can view the arabic national day form at sa96', function () {
    $this->get('/sa96')
        ->assertSuccessful()
        ->assertSee('اليوم الوطني السعودي 96', false)
        ->assertSee('أوافق على استخدام رقم جوالي لإكمال عملية التحقق', false)
        ->assertSee('name="birth_year"', false)
        ->assertSee('name="name"', false)
        ->assertSee('name="phone"', false)
        ->assertSee('name="consent"', false)
        ->assertDontSee('name="consent_campaign"', false)
        ->assertDontSee('name="consent_marketing"', false)
        ->assertDontSee('checked', false);
});

test('guest can view the english national day form', function () {
    $this->get('/sa96?lang=en')
        ->assertSuccessful()
        ->assertSee('Saudi National Day 96', false)
        ->assertSee('Year of birth', false)
        ->assertSee('I agree to using my mobile number to complete verification', false);
});

test('privacy notice discloses controller purposes rights and sdaia', function () {
    $this->get('/sa96/privacy')
        ->assertSuccessful()
        ->assertSee('إشعار الخصوصية', false)
        ->assertSee(config('sa96.privacy_email'), false)
        ->assertSee('سدايا', false);
});

test('visitor receives authentica otp before registration is stored', function () {
    mockSa96OtpSend();

    $this->post('/sa96', validSa96Payload())
        ->assertRedirect(route('sa96.create', ['lang' => 'ar']))
        ->assertSessionHas('status')
        ->assertSessionHas('sa96.pending.phone_e164', '+966551234567');

    expect(Sa96Registration::query()->count())->toBe(0);

    $this->get('/sa96')
        ->assertSuccessful()
        ->assertSee('رمز التحقق', false)
        ->assertSee('+966551234567', false);
});

test('visitor can complete registration after verifying authentica otp', function () {
    $this->mock(Otp::class, function ($mock): void {
        $mock->shouldReceive('send')->once()->with('+966551234567');
        $mock->shouldReceive('verify')->once()->with('+966551234567', '1234')->andReturn(true);
    });

    $this->post('/sa96', validSa96Payload())->assertRedirect();

    $this->post('/sa96/otp/verify', [
        'otp' => '1234',
        'lang' => 'ar',
    ])
        ->assertRedirect(route('sa96.thanks', ['lang' => 'ar']))
        ->assertSessionHas('sa96_registered', true)
        ->assertSessionMissing('sa96.pending');

    $registration = Sa96Registration::query()->first();

    expect($registration)->not->toBeNull()
        ->and($registration->name)->toBe('سارة أحمد')
        ->and($registration->phone)->toBe('+966551234567')
        ->and($registration->phone_hash)->toBe(Sa96Privacy::phoneHash('+966551234567'))
        ->and($registration->birth_year)->toBe(1995)
        ->and($registration->consent_privacy_notice)->toBeTrue()
        ->and($registration->consent_campaign)->toBeTrue()
        ->and($registration->consent_capacity)->toBeTrue()
        ->and($registration->consent_cross_border)->toBeTrue()
        ->and($registration->consent_marketing)->toBeTrue()
        ->and($registration->consent_method)->toBe('web_form_otp')
        ->and($registration->consent_notice_version)->toBe(config('sa96.notice_version'))
        ->and($registration->consent_snapshot['agree'])->toBe(config('sa96.consents.ar.agree'));
});

test('invalid otp does not create a registration', function () {
    $this->mock(Otp::class, function ($mock): void {
        $mock->shouldReceive('send')->once()->with('+966551234567');
        $mock->shouldReceive('verify')->once()->with('+966551234567', '0000')->andReturn(false);
    });

    $this->post('/sa96', validSa96Payload())->assertRedirect();

    $this->from(route('sa96.create'))
        ->post('/sa96/otp/verify', ['otp' => '0000', 'lang' => 'ar'])
        ->assertRedirect(route('sa96.create', ['lang' => 'ar']))
        ->assertSessionHasErrors('otp');

    expect(Sa96Registration::query()->count())->toBe(0);
});

test('visitor can resend authentica otp after cooldown', function () {
    $this->mock(Otp::class, function ($mock): void {
        $mock->shouldReceive('send')->twice()->with('+966551234567');
    });

    $this->post('/sa96', validSa96Payload())->assertRedirect();

    session(['sa96.pending' => array_merge(session('sa96.pending'), [
        'otp_sent_at' => now()->subSeconds(31)->timestamp,
    ])]);

    $this->post('/sa96/otp/resend', ['lang' => 'ar'])
        ->assertRedirect()
        ->assertSessionHas('status');
});

test('visitor can cancel otp and return to the form', function () {
    mockSa96OtpSend();

    $this->post('/sa96', validSa96Payload())->assertRedirect();

    $this->post('/sa96/otp/cancel', ['lang' => 'ar'])
        ->assertRedirect(route('sa96.create', ['lang' => 'ar']))
        ->assertSessionMissing('sa96.pending');
});

test('registration is rejected without the consent checkbox', function () {
    $payload = validSa96Payload();
    unset($payload['consent']);

    $this->from('/sa96')->post('/sa96', $payload)
        ->assertRedirect('/sa96')
        ->assertSessionHasErrors('consent');

    expect(Sa96Registration::query()->count())->toBe(0);
});

test('registration is rejected when the visitor is under eighteen', function () {
    $this->from('/sa96')->post('/sa96', validSa96Payload([
        'birth_year' => (string) (now()->year - 17),
    ]))->assertSessionHasErrors('birth_year');

    expect(Sa96Registration::query()->count())->toBe(0);
});

test('registration is rejected for an invalid phone number', function () {
    $this->from('/sa96')->post('/sa96', validSa96Payload([
        'phone' => '12345',
    ]))->assertSessionHasErrors('phone');
});

test('arabic birth year digits are accepted before otp is sent', function () {
    mockSa96OtpSend();

    $this->post('/sa96', validSa96Payload([
        'birth_year' => '١٩٩٥',
    ]))
        ->assertRedirect(route('sa96.create', ['lang' => 'ar']))
        ->assertSessionHas('sa96.pending.birth_year', 1995);
});

test('honeypot submissions do not store personal data or send otp', function () {
    $this->mock(Otp::class)->shouldNotReceive('send');

    $this->post('/sa96', validSa96Payload([
        'website' => 'https://spam.example',
    ]))->assertRedirect(route('sa96.thanks', ['lang' => 'ar']));

    expect(Sa96Registration::query()->count())->toBe(0);
});

test('resubmitting the same phone after otp updates the record instead of duplicating', function () {
    $this->mock(Otp::class, function ($mock): void {
        $mock->shouldReceive('send')->twice()->with('+966551234567');
        $mock->shouldReceive('verify')->twice()->with('+966551234567', '1234')->andReturn(true);
    });

    $this->post('/sa96', validSa96Payload())->assertRedirect();
    $this->post('/sa96/otp/verify', ['otp' => '1234', 'lang' => 'ar'])->assertRedirect();

    $this->post('/sa96', validSa96Payload(['name' => 'سارة']))->assertRedirect();
    $this->post('/sa96/otp/verify', ['otp' => '1234', 'lang' => 'ar'])->assertRedirect();

    expect(Sa96Registration::query()->count())->toBe(1)
        ->and(Sa96Registration::query()->first()->name)->toBe('سارة');
});

test('thanks page requires a completed registration session', function () {
    $this->get('/sa96/thanks')->assertRedirect(route('sa96.create', ['lang' => 'ar']));
});

test('visitor can withdraw consent and personal data is destroyed', function () {
    $registration = Sa96Registration::factory()->create([
        'phone' => '+966551234567',
        'phone_hash' => Sa96Privacy::phoneHash('+966551234567'),
        'name' => 'Sara',
        'birth_year' => 1990,
    ]);

    $this->post('/sa96/withdraw', [
        'phone' => '0551234567',
        'confirm_withdraw' => '1',
        'lang' => 'en',
    ])->assertRedirect(route('sa96.withdraw', ['lang' => 'en']));

    $registration->refresh();

    expect($registration->isWithdrawn())->toBeTrue()
        ->and($registration->name)->toBeNull()
        ->and($registration->phone)->toBeNull()
        ->and($registration->birth_year)->toBeNull()
        ->and($registration->ip_address)->toBeNull()
        ->and($registration->user_agent)->toBeNull();
});

test('withdrawing an unknown number still succeeds without revealing whether it existed', function () {
    $this->post('/sa96/withdraw', [
        'phone' => '0559999999',
        'confirm_withdraw' => '1',
        'lang' => 'en',
    ])->assertRedirect(route('sa96.withdraw', ['lang' => 'en']))
        ->assertSessionHas('status');
});

test('expired and withdrawn registrations are purged on schedule', function () {
    Sa96Registration::factory()->create([
        'created_at' => now()->subDays((int) config('sa96.retention_days') + 1),
    ]);

    $recent = Sa96Registration::factory()->create();

    Sa96Registration::factory()->withdrawn()->create([
        'withdrawn_at' => now()->subDays((int) config('sa96.withdrawn_retention_days') + 1),
    ]);

    $this->artisan('sa96:purge-expired')->assertSuccessful();

    expect(Sa96Registration::query()->count())->toBe(1)
        ->and($recent->fresh())->not->toBeNull();
});

test('purge command is scheduled daily', function () {
    $schedule = app(Schedule::class);

    $event = collect($schedule->events())->first(
        fn ($event) => str_contains($event->command ?? '', 'sa96:purge-expired')
    );

    expect($event)->not->toBeNull();
    expect($event->expression)->toBe('0 0 * * *');
});
