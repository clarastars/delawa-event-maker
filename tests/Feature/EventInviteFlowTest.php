<?php

use App\Contracts\Otp;
use App\Models\Contact;
use App\Models\Event;
use App\Models\Voucher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

test('event invite page renders the event name when no banner is uploaded', function () {
    $event = Event::factory()->create(['name' => 'Ramadan Campaign']);

    $this->get(route('event.invite', ['event' => $event, 'lang' => 'en']))
        ->assertSuccessful()
        ->assertSee('Ramadan Campaign')
        ->assertSee('Send verification code');
});

test('event invite page shows the uploaded banner', function () {
    $event = Event::factory()->create(['banner_path' => 'event-banners/banner.png']);

    $this->get(route('event.invite', $event))
        ->assertSuccessful()
        ->assertSee('event-banners/banner.png', false);
});

test('event invite page shows terms with line breaks', function () {
    $event = Event::factory()->create([
        'terms' => "First line\nSecond line",
    ]);

    $this->get(route('event.invite', ['event' => $event, 'lang' => 'en']))
        ->assertSuccessful()
        ->assertSee('Terms')
        ->assertSee('First line')
        ->assertSee('Second line')
        ->assertSee('whitespace-pre-line', false);

    $this->get(route('event.invite', ['event' => $event, 'lang' => 'ar']))
        ->assertSuccessful()
        ->assertSee('الشروط');
});

test('event invite page hides terms when they are empty', function () {
    $event = Event::factory()->create(['terms' => null]);

    $this->get(route('event.invite', ['event' => $event, 'lang' => 'en']))
        ->assertSuccessful()
        ->assertDontSee('>Terms</h2>', false);
});

test('event invite page escapes html in terms', function () {
    $event = Event::factory()->create([
        'terms' => '<script>alert(1)</script>',
    ]);

    $this->get(route('event.invite', $event))
        ->assertSuccessful()
        ->assertDontSee('<script>alert(1)</script>', false)
        ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false);
});

test('unknown event slug returns 404', function () {
    $this->get('/e/nope1234')->assertNotFound();
});

test('unknown phone number sees a not found message', function () {
    $event = Event::factory()->create();

    $this->post(route('event.otp.send', $event), [
        'phone' => '+966551234567',
        'lang' => 'en',
    ])
        ->assertSuccessful()
        ->assertSee('We could not find an active coupon for this phone number');
});

test('contact with multiple vouchers sees all of them after otp verification', function () {
    $this->mock(Otp::class, function ($mock): void {
        $mock->shouldReceive('send')->once()->with('+966551234567');
        $mock->shouldReceive('verify')->once()->with('+966551234567', '1234')->andReturn(true);
    });

    $event = Event::factory()->create();
    $otherEvent = Event::factory()->create();

    $contact = Contact::create([
        'name' => 'Sara',
        'phone' => '+966 55 123 4567',
        'phone_normalized' => Contact::normalizePhone('+966 55 123 4567'),
    ]);

    foreach (['EG-SA-100', 'EG-SA-101'] as $voucherId) {
        Voucher::create([
            'contact_id' => $contact->id,
            'event_id' => $event->id,
            'voucher_id' => $voucherId,
            'creation_date' => now()->toDateString(),
            'expiry_date' => now()->addMonth()->toDateString(),
            'balance' => 250,
            'status' => Voucher::STATUS_ACTIVE,
            'one_time_redemption' => true,
        ]);
    }

    Voucher::create([
        'contact_id' => $contact->id,
        'event_id' => $otherEvent->id,
        'voucher_id' => 'OTHER-999',
        'creation_date' => now()->toDateString(),
        'balance' => 250,
        'status' => Voucher::STATUS_ACTIVE,
        'one_time_redemption' => true,
    ]);

    $this->post(route('event.otp.send', $event), [
        'name' => 'Sara',
        'phone' => '+966 55 123 4567',
        'lang' => 'en',
    ])->assertRedirect(route('event.invite', ['event' => $event, 'lang' => 'en']));

    $this->post(route('event.otp.verify', $event), [
        'otp' => '1234',
        'lang' => 'en',
    ])->assertRedirect(route('event.vouchers', ['event' => $event, 'lang' => 'en']));

    $this->get(route('event.vouchers', ['event' => $event, 'lang' => 'en']))
        ->assertSuccessful()
        ->assertSee('EG-SA-100')
        ->assertSee('EG-SA-101')
        ->assertDontSee('OTHER-999');

    expect($contact->fresh()->activated_at)->not->toBeNull();
});

test('contact with event entries can see products and claim a voucher', function () {
    $this->mock(Otp::class, function ($mock): void {
        $mock->shouldReceive('send')->once()->with('+966551234567');
        $mock->shouldReceive('verify')->once()->with('+966551234567', '1234')->andReturn(true);
    });

    $event = Event::factory()->create();
    $product = $event->products()->create([
        'name' => 'Gift Box',
    ]);

    $contact = Contact::create([
        'name' => 'Sara',
        'phone' => '+966 55 123 4567',
        'phone_normalized' => Contact::normalizePhone('+966 55 123 4567'),
    ]);

    // Grant 1 entry to the contact for the event
    $contact->events()->attach($event, ['entries' => 1]);

    $voucher = Voucher::create([
        'contact_id' => null, // Unassigned
        'event_id' => $event->id,
        'product_id' => $product->id,
        'voucher_id' => 'GIFT-100',
        'creation_date' => now()->toDateString(),
        'balance' => 250,
        'status' => Voucher::STATUS_ACTIVE,
        'one_time_redemption' => true,
    ]);

    // Send OTP
    $this->post(route('event.otp.send', $event), [
        'name' => 'Sara',
        'phone' => '+966 55 123 4567',
        'lang' => 'en',
    ])->assertRedirect(route('event.invite', ['event' => $event, 'lang' => 'en']));

    // Verify OTP
    $this->post(route('event.otp.verify', $event), [
        'otp' => '1234',
        'lang' => 'en',
    ])->assertRedirect(route('event.vouchers', ['event' => $event, 'lang' => 'en']));

    // See Product Selection
    $this->get(route('event.vouchers', ['event' => $event, 'lang' => 'en']))
        ->assertSuccessful()
        ->assertSee('Gift Box')
        ->assertSee('Choose your product')
        ->assertDontSee('GIFT-100'); // Voucher shouldn't be shown yet because it's not claimed

    // Claim Product
    $this->post(route('event.products.claim', ['event' => $event, 'product' => $product, 'lang' => 'en']))
        ->assertRedirect(route('event.vouchers', ['event' => $event, 'lang' => 'en']));

    // Now the voucher should be displayed and the product should be gone since 1 entry was claimed
    $this->get(route('event.vouchers', ['event' => $event, 'lang' => 'en']))
        ->assertSuccessful()
        ->assertSee('GIFT-100')
        ->assertSee('data-voucher-qr="GIFT-100"', false)
        ->assertDontSee('Choose your product');

    $this->assertDatabaseHas('vouchers', [
        'id' => $voucher->id,
        'contact_id' => $contact->id,
    ]);
});

test('contact without an assigned voucher is never given one automatically', function () {
    $event = Event::factory()->create();

    Contact::create([
        'name' => 'Sara',
        'phone' => '0551234567',
        'phone_normalized' => Contact::normalizePhone('0551234567'),
    ]);

    $poolVoucher = Voucher::create([
        'event_id' => $event->id,
        'voucher_id' => 'POOL-001',
        'creation_date' => now()->toDateString(),
        'balance' => 250,
        'status' => Voucher::STATUS_ACTIVE,
        'one_time_redemption' => true,
    ]);

    $this->post(route('event.otp.send', $event), [
        'phone' => '0551234567',
        'lang' => 'en',
    ])
        ->assertSuccessful()
        ->assertSee('We could not find an active coupon for this phone number');

    $this->assertDatabaseHas('vouchers', [
        'id' => $poolVoucher->id,
        'contact_id' => null,
    ]);
});

test('vouchers page redirects to the invite form without a verified session', function () {
    $event = Event::factory()->create();

    $this->get(route('event.vouchers', $event))
        ->assertRedirect(route('event.invite', ['event' => $event, 'lang' => 'ar']));
});

test('invite and otp still work before the event starts', function () {
    $this->travelTo(Carbon::parse('2026-09-21 23:00:00', 'Asia/Riyadh'));

    $this->mock(Otp::class, function ($mock): void {
        $mock->shouldReceive('send')->once()->with('+966551234567');
        $mock->shouldReceive('verify')->once()->with('+966551234567', '1234')->andReturn(true);
    });

    $event = Event::factory()->create([
        'starts_at' => '2026-09-22 00:00:00',
        'ends_at' => '2026-09-23 00:00:00',
    ]);

    $contact = Contact::create([
        'name' => 'Sara',
        'phone' => '+966 55 123 4567',
        'phone_normalized' => Contact::normalizePhone('+966 55 123 4567'),
    ]);

    Voucher::create([
        'contact_id' => $contact->id,
        'event_id' => $event->id,
        'voucher_id' => 'EG-SA-100',
        'creation_date' => now()->toDateString(),
        'balance' => 250,
        'status' => Voucher::STATUS_ACTIVE,
        'one_time_redemption' => true,
    ]);

    $this->get(route('event.invite', ['event' => $event, 'lang' => 'en']))
        ->assertSuccessful()
        ->assertSee('Send verification code')
        ->assertDontSee('This event has ended');

    $this->post(route('event.otp.send', $event), [
        'name' => 'Sara',
        'phone' => '+966 55 123 4567',
        'lang' => 'en',
    ])->assertRedirect(route('event.invite', ['event' => $event, 'lang' => 'en']));

    $this->post(route('event.otp.verify', $event), [
        'otp' => '1234',
        'lang' => 'en',
    ])->assertRedirect(route('event.vouchers', ['event' => $event, 'lang' => 'en']));
});

test('logged in guest does not see vouchers before the event starts', function () {
    $this->travelTo(Carbon::parse('2026-09-21 23:00:00', 'Asia/Riyadh'));

    $event = Event::factory()->create([
        'starts_at' => '2026-09-22 00:00:00',
        'ends_at' => '2026-09-23 00:00:00',
    ]);

    $contact = Contact::create([
        'name' => 'Sara',
        'phone' => '+966 55 123 4567',
        'phone_normalized' => Contact::normalizePhone('+966 55 123 4567'),
    ]);

    Voucher::create([
        'contact_id' => $contact->id,
        'event_id' => $event->id,
        'voucher_id' => 'EG-SA-100',
        'creation_date' => now()->toDateString(),
        'balance' => 250,
        'status' => Voucher::STATUS_ACTIVE,
        'one_time_redemption' => true,
    ]);

    $this->withSession([
        "event_invite.{$event->id}.verified_contact_id" => $contact->id,
    ])->get(route('event.vouchers', ['event' => $event, 'lang' => 'en']))
        ->assertSuccessful()
        ->assertSee('Vouchers will be available to view from')
        ->assertSee('Tuesday, 22 September 2026, 12:00 AM')
        ->assertDontSee('EG-SA-100');

    $this->withSession([
        "event_invite.{$event->id}.verified_contact_id" => $contact->id,
    ])->get(route('event.vouchers', ['event' => $event, 'lang' => 'ar']))
        ->assertSuccessful()
        ->assertSee('ستتوفر القسائم للعرض ابتداءً من')
        ->assertDontSee('EG-SA-100');
});

test('logged in guest sees vouchers during the scheduled window', function () {
    $this->travelTo(Carbon::parse('2026-09-22 10:00:00', 'Asia/Riyadh'));

    $event = Event::factory()->create([
        'starts_at' => '2026-09-22 00:00:00',
        'ends_at' => '2026-09-23 00:00:00',
    ]);

    $contact = Contact::create([
        'name' => 'Sara',
        'phone' => '+966 55 123 4567',
        'phone_normalized' => Contact::normalizePhone('+966 55 123 4567'),
    ]);

    Voucher::create([
        'contact_id' => $contact->id,
        'event_id' => $event->id,
        'voucher_id' => 'EG-SA-100',
        'creation_date' => now()->toDateString(),
        'balance' => 250,
        'status' => Voucher::STATUS_ACTIVE,
        'one_time_redemption' => true,
    ]);

    $this->withSession([
        "event_invite.{$event->id}.verified_contact_id" => $contact->id,
    ])->get(route('event.vouchers', ['event' => $event, 'lang' => 'en']))
        ->assertSuccessful()
        ->assertSee('EG-SA-100')
        ->assertDontSee('Vouchers will be available to view from');
});

test('events without start and end dates still show vouchers after login', function () {
    $event = Event::factory()->create([
        'starts_at' => null,
        'ends_at' => null,
    ]);

    $contact = Contact::create([
        'name' => 'Sara',
        'phone' => '+966 55 123 4567',
        'phone_normalized' => Contact::normalizePhone('+966 55 123 4567'),
    ]);

    Voucher::create([
        'contact_id' => $contact->id,
        'event_id' => $event->id,
        'voucher_id' => 'EG-SA-100',
        'creation_date' => now()->toDateString(),
        'balance' => 250,
        'status' => Voucher::STATUS_ACTIVE,
        'one_time_redemption' => true,
    ]);

    $this->withSession([
        "event_invite.{$event->id}.verified_contact_id" => $contact->id,
    ])->get(route('event.vouchers', ['event' => $event, 'lang' => 'en']))
        ->assertSuccessful()
        ->assertSee('EG-SA-100');
});

test('claiming a product before the event starts does not assign a voucher', function () {
    $this->travelTo(Carbon::parse('2026-09-21 23:00:00', 'Asia/Riyadh'));

    $event = Event::factory()->create([
        'starts_at' => '2026-09-22 00:00:00',
        'ends_at' => '2026-09-23 00:00:00',
    ]);
    $product = $event->products()->create([
        'name' => 'Gift Box',
    ]);

    $contact = Contact::create([
        'name' => 'Sara',
        'phone' => '+966 55 123 4567',
        'phone_normalized' => Contact::normalizePhone('+966 55 123 4567'),
    ]);

    $contact->events()->attach($event, ['entries' => 1]);

    $voucher = Voucher::create([
        'contact_id' => null,
        'event_id' => $event->id,
        'product_id' => $product->id,
        'voucher_id' => 'GIFT-100',
        'creation_date' => now()->toDateString(),
        'balance' => 250,
        'status' => Voucher::STATUS_ACTIVE,
        'one_time_redemption' => true,
    ]);

    $this->withSession([
        "event_invite.{$event->id}.verified_contact_id" => $contact->id,
    ])->post(route('event.products.claim', ['event' => $event, 'product' => $product, 'lang' => 'en']))
        ->assertRedirect(route('event.vouchers', ['event' => $event, 'lang' => 'en']));

    expect($voucher->fresh()->contact_id)->toBeNull();
});
