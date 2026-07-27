<?php

use App\Contracts\Otp;
use App\Models\Contact;
use App\Models\Voucher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->markTestSkipped('The event has ended and the invite flow is disabled.');
});

test('accept page shows debug otp warning when debug mode is enabled', function () {
    config([
        'services.authentica.debug_otp' => true,
        'services.authentica.debug_otp_code' => '1234',
    ]);

    $this->get('/accept?lang=en')
        ->assertSuccessful()
        ->assertSee('Debug mode on: use 1234 for OTP', false);
});

test('accept page hides debug otp warning in production', function () {
    config(['services.authentica.debug_otp' => false]);

    $this->get('/accept?lang=en')
        ->assertSuccessful()
        ->assertDontSee('Debug mode on', false);
});

test('guest can view the bilingual invite page', function () {
    $this->get('/accept')
        ->assertSuccessful()
        ->assertSee('property="og:image"', false)
        ->assertSee('invitation.webp', false)
        ->assertSee('invitation.webp', false)
        ->assertSee('إرسال رمز التحقق')
        ->assertSee('dir="ltr"', false)
        ->assertSee('English')
        ->assertSee('Al Narjis')
        ->assertSee('فرع النرجس');
});

test('guest can reveal an active voucher after otp verification', function () {
    $this->mock(Otp::class, function ($mock): void {
        $mock->shouldReceive('send')->once()->with('+966551234567');
        $mock->shouldReceive('verify')->once()->with('+966551234567', '1234')->andReturn(true);
    });

    $contact = Contact::create([
        'name' => 'Sara',
        'email' => 'sara@example.com',
        'phone' => '+966 55 123 4567',
        'phone_normalized' => Contact::normalizePhone('+966 55 123 4567'),
    ]);

    $expiryDate = now()->addMonth();

    Voucher::create([
        'contact_id' => $contact->id,
        'voucher_id' => 'EG-SA-100',
        'creation_date' => now()->toDateString(),
        'expiry_date' => $expiryDate->toDateString(),
        'balance' => 250,
        'status' => Voucher::STATUS_ACTIVE,
        'one_time_redemption' => true,
    ]);

    $this->post('/accept/otp/send', [
        'name' => 'Sara',
        'phone' => '+966 55 123 4567',
        'lang' => 'en',
    ])->assertRedirect(route('accept.index', ['lang' => 'en']));

    $this->post('/accept/otp/verify', [
        'otp' => '1234',
        'lang' => 'en',
    ])
        ->assertRedirect(route('accept.voucher.show', ['lang' => 'en']));

    $this->get(route('accept.voucher.show', ['lang' => 'en']))
        ->assertSuccessful()
        ->assertSee('id="voucher-card"', false)
        ->assertSee('voucher.webp', false)
        ->assertSee('EG-SA-100')
        ->assertSee('Download voucher')
        ->assertSee('Al Narjis')
        ->assertSee('فرع النرجس');

    expect($contact->fresh()->activated_at)->not->toBeNull();
});

test('voucher page shows remaining balance from tsepass api', function () {
    config([
        'services.tsepass.api_url' => 'https://api.tsepass.test',
        'services.tsepass.api_key' => 'test-key',
    ]);

    Http::fake([
        'https://api.tsepass.test/queries/gift-card-simple*' => Http::response([
            'success' => true,
            'queryName' => 'gift-card-simple',
            'count' => 1,
            'data' => [
                [
                    'originalAmount' => 400,
                    'netCardValue' => 275.5,
                ],
            ],
        ]),
    ]);

    $this->mock(Otp::class, function ($mock): void {
        $mock->shouldReceive('send')->once();
        $mock->shouldReceive('verify')->once()->andReturn(true);
    });

    $contact = Contact::create([
        'name' => 'Sara',
        'email' => 'sara@example.com',
        'phone' => '0551234567',
        'phone_normalized' => Contact::normalizePhone('0551234567'),
    ]);

    Voucher::create([
        'contact_id' => $contact->id,
        'voucher_id' => 'EG-SA-100',
        'creation_date' => now()->toDateString(),
        'expiry_date' => now()->addMonth()->toDateString(),
        'balance' => 250,
        'status' => Voucher::STATUS_ACTIVE,
        'one_time_redemption' => true,
    ]);

    $this->post('/accept/otp/send', [
        'name' => 'Sara',
        'phone' => '0551234567',
        'lang' => 'en',
    ]);

    $this->post('/accept/otp/verify', [
        'otp' => '1234',
        'lang' => 'en',
    ]);

    $this->get(route('accept.voucher.show', ['lang' => 'en']))
        ->assertSuccessful()
        ->assertSee('Remaining balance')
        ->assertSee('275.50')
        ->assertSee('SAR');

    Http::assertSent(function ($request): bool {
        return $request->url() === 'https://api.tsepass.test/queries/gift-card-simple?cardNumber=EG-SA-100'
            && $request->hasHeader('x-api-key', 'test-key');
    });
});

test('voucher page hides balance when tsepass api fails', function () {
    config([
        'services.tsepass.api_url' => 'https://api.tsepass.test',
        'services.tsepass.api_key' => 'test-key',
    ]);

    Http::fake([
        'https://api.tsepass.test/queries/gift-card-simple*' => Http::response([], 500),
    ]);

    $this->mock(Otp::class, function ($mock): void {
        $mock->shouldReceive('send')->once();
        $mock->shouldReceive('verify')->once()->andReturn(true);
    });

    $contact = Contact::create([
        'name' => 'Sara',
        'email' => 'sara@example.com',
        'phone' => '0551234567',
        'phone_normalized' => Contact::normalizePhone('0551234567'),
    ]);

    Voucher::create([
        'contact_id' => $contact->id,
        'voucher_id' => 'EG-SA-100',
        'creation_date' => now()->toDateString(),
        'expiry_date' => now()->addMonth()->toDateString(),
        'balance' => 250,
        'status' => Voucher::STATUS_ACTIVE,
        'one_time_redemption' => true,
    ]);

    $this->post('/accept/otp/send', [
        'name' => 'Sara',
        'phone' => '0551234567',
        'lang' => 'en',
    ]);

    $this->post('/accept/otp/verify', [
        'otp' => '1234',
        'lang' => 'en',
    ]);

    $this->get(route('accept.voucher.show', ['lang' => 'en']))
        ->assertSuccessful()
        ->assertSee('EG-SA-100')
        ->assertDontSee('Remaining balance');
});

test('viewing voucher again does not change activated_at', function () {
    $this->mock(Otp::class, function ($mock): void {
        $mock->shouldReceive('send')->once()->with('+966551234567');
        $mock->shouldReceive('verify')->once()->with('+966551234567', '1234')->andReturn(true);
    });

    $contact = Contact::create([
        'name' => 'Sara',
        'email' => 'sara@example.com',
        'phone' => '+966 55 123 4567',
        'phone_normalized' => Contact::normalizePhone('+966 55 123 4567'),
    ]);

    Voucher::create([
        'contact_id' => $contact->id,
        'voucher_id' => 'EG-SA-100',
        'creation_date' => now()->toDateString(),
        'expiry_date' => now()->addMonth()->toDateString(),
        'balance' => 250,
        'status' => Voucher::STATUS_ACTIVE,
        'one_time_redemption' => true,
    ]);

    $this->post('/accept/otp/send', [
        'name' => 'Sara',
        'phone' => '+966 55 123 4567',
        'lang' => 'en',
    ]);

    $this->post('/accept/otp/verify', ['otp' => '1234', 'lang' => 'en']);

    $this->get(route('accept.voucher.show', ['lang' => 'en']));

    $activatedAt = $contact->fresh()->activated_at;

    $this->get(route('accept.voucher.show', ['lang' => 'en']));

    expect($contact->fresh()->activated_at)->toEqual($activatedAt);
});

test('guest does not see inactive voucher', function () {
    $this->mock(Otp::class)->shouldNotReceive('send');

    $contact = Contact::create([
        'name' => 'Sara',
        'email' => 'sara@example.com',
        'phone' => '0551234567',
        'phone_normalized' => Contact::normalizePhone('0551234567'),
    ]);

    Voucher::create([
        'contact_id' => $contact->id,
        'voucher_id' => 'INACTIVE-100',
        'creation_date' => now()->toDateString(),
        'expiry_date' => now()->addMonth()->toDateString(),
        'balance' => 250,
        'status' => Voucher::STATUS_INACTIVE,
        'one_time_redemption' => true,
    ]);

    $this->post('/accept/otp/send', [
        'name' => 'Sara',
        'phone' => '0551234567',
        'lang' => 'en',
    ])
        ->assertSuccessful()
        ->assertDontSee('INACTIVE-100')
        ->assertSee('could not find an active voucher');
});

test('guest can reveal voucher with phone only when name does not match', function () {
    $this->mock(Otp::class, function ($mock): void {
        $mock->shouldReceive('send')->once();
        $mock->shouldReceive('verify')->once()->andReturn(true);
    });

    $contact = Contact::create([
        'name' => 'Sara',
        'email' => 'sara@example.com',
        'phone' => '0551234567',
        'phone_normalized' => Contact::normalizePhone('0551234567'),
    ]);

    Voucher::create([
        'contact_id' => $contact->id,
        'voucher_id' => 'EG-SA-100',
        'creation_date' => now()->toDateString(),
        'expiry_date' => now()->addMonth()->toDateString(),
        'balance' => 250,
        'status' => Voucher::STATUS_ACTIVE,
        'one_time_redemption' => true,
    ]);

    $this->post('/accept/otp/send', [
        'phone' => '0551234567',
        'lang' => 'en',
    ])->assertRedirect(route('accept.index', ['lang' => 'en']));

    $this->post('/accept/otp/verify', [
        'otp' => '1234',
        'lang' => 'en',
    ])
        ->assertRedirect(route('accept.voucher.show', ['lang' => 'en']));

    $this->get(route('accept.voucher.show', ['lang' => 'en']))
        ->assertSuccessful()
        ->assertSee('EG-SA-100');
});

test('guest does not see voucher for unknown phone number', function () {
    $this->mock(Otp::class)->shouldNotReceive('send');

    $this->post('/accept/otp/send', [
        'phone' => '0559999999',
        'lang' => 'en',
    ])
        ->assertSuccessful()
        ->assertDontSee('EG-SA-100')
        ->assertSee('could not find an active voucher');
});

test('guest without assigned voucher can receive one via otp', function () {
    $this->mock(Otp::class, function ($mock): void {
        $mock->shouldReceive('send')->once();
        $mock->shouldReceive('verify')->once()->andReturn(true);
    });
    $contact = Contact::create([
        'name' => 'Sara',
        'phone' => '+966 55 123 4567',
        'phone_normalized' => Contact::normalizePhone('+966 55 123 4567'),
    ]);
    Voucher::create([
        'voucher_id' => 'EG-SA-POOL-001',
        'creation_date' => now()->toDateString(),
        'expiry_date' => now()->addMonth()->toDateString(),
        'balance' => 400,
        'status' => Voucher::STATUS_ACTIVE,
        'one_time_redemption' => true,
    ]);
    $this->post('/accept/otp/send', ['phone' => '+966 55 123 4567', 'lang' => 'en'])
        ->assertRedirect(route('accept.index', ['lang' => 'en']));
    expect($contact->fresh()->voucher?->voucher_id)->toBe('EG-SA-POOL-001');
    $this->post('/accept/otp/verify', ['otp' => '1234', 'lang' => 'en'])
        ->assertRedirect(route('accept.voucher.show', ['lang' => 'en']));
    $this->get(route('accept.voucher.show', ['lang' => 'en']))->assertSee('EG-SA-POOL-001');
});

test('guest cannot resend otp before cooldown elapses', function () {
    $this->mock(Otp::class, function ($mock): void {
        $mock->shouldReceive('send')->once()->with('+966551234567');
    });

    $contact = Contact::create([
        'name' => 'Sara',
        'email' => 'sara@example.com',
        'phone' => '+966 55 123 4567',
        'phone_normalized' => Contact::normalizePhone('+966 55 123 4567'),
    ]);

    Voucher::create([
        'contact_id' => $contact->id,
        'voucher_id' => 'EG-SA-100',
        'creation_date' => now()->toDateString(),
        'expiry_date' => now()->addMonth()->toDateString(),
        'balance' => 250,
        'status' => Voucher::STATUS_ACTIVE,
        'one_time_redemption' => true,
    ]);

    $this->post('/accept/otp/send', [
        'name' => 'Sara',
        'phone' => '+966 55 123 4567',
        'lang' => 'en',
    ])->assertRedirect(route('accept.index', ['lang' => 'en']));

    $this->post('/accept/otp/resend', ['lang' => 'en'])
        ->assertRedirect()
        ->assertSessionHasErrors('otp');
});

test('guest can resend otp after cooldown elapses', function () {
    $this->mock(Otp::class, function ($mock): void {
        $mock->shouldReceive('send')->twice()->with('+966551234567');
    });

    $contact = Contact::create([
        'name' => 'Sara',
        'email' => 'sara@example.com',
        'phone' => '+966 55 123 4567',
        'phone_normalized' => Contact::normalizePhone('+966 55 123 4567'),
    ]);

    Voucher::create([
        'contact_id' => $contact->id,
        'voucher_id' => 'EG-SA-100',
        'creation_date' => now()->toDateString(),
        'expiry_date' => now()->addMonth()->toDateString(),
        'balance' => 250,
        'status' => Voucher::STATUS_ACTIVE,
        'one_time_redemption' => true,
    ]);

    $this->post('/accept/otp/send', [
        'name' => 'Sara',
        'phone' => '+966 55 123 4567',
        'lang' => 'en',
    ]);

    $this->travel(30)->seconds();

    $this->post('/accept/otp/resend', ['lang' => 'en'])
        ->assertRedirect()
        ->assertSessionHas('status');
});

test('guest sees error when otp verification fails', function () {
    $this->mock(Otp::class, function ($mock): void {
        $mock->shouldReceive('send')->once();
        $mock->shouldReceive('verify')->once()->andReturn(false);
    });

    $contact = Contact::create([
        'name' => 'Sara',
        'email' => 'sara@example.com',
        'phone' => '0551234567',
        'phone_normalized' => Contact::normalizePhone('0551234567'),
    ]);

    Voucher::create([
        'contact_id' => $contact->id,
        'voucher_id' => 'EG-SA-100',
        'creation_date' => now()->toDateString(),
        'expiry_date' => now()->addMonth()->toDateString(),
        'balance' => 250,
        'status' => Voucher::STATUS_ACTIVE,
        'one_time_redemption' => true,
    ]);

    $this->post('/accept/otp/send', [
        'name' => 'Sara',
        'phone' => '0551234567',
        'lang' => 'en',
    ]);

    $this->post('/accept/otp/verify', [
        'otp' => '9999',
        'lang' => 'en',
    ])
        ->assertSuccessful()
        ->assertDontSee('EG-SA-100')
        ->assertSee('verification code is invalid');
});
