<?php

use App\Contracts\Otp;
use App\Models\Contact;
use App\Models\Event;
use App\Models\Voucher;
use Illuminate\Foundation\Testing\RefreshDatabase;

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
