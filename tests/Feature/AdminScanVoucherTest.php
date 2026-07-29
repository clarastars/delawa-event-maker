<?php

use App\Models\Contact;
use App\Models\Event;
use App\Models\User;
use App\Models\Voucher;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('shows the scan page for authenticated admin', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('admin.scan.index'))
        ->assertOk()
        ->assertSee('Scan Barcode');
});

it('requires authentication to access scan page', function () {
    $this->get(route('admin.scan.index'))
        ->assertRedirect(route('admin.login'));
});

it('can scan and redeem an active voucher', function () {
    $user = User::factory()->create();

    $event = Event::create([
        'name' => 'Test Event',
        'slug' => 'test-event',
        'is_open' => true,
    ]);

    $contact = Contact::create([
        'name' => 'John Doe',
        'phone' => '1234567890',
        'phone_normalized' => '1234567890',
    ]);

    $voucher = Voucher::create([
        'event_id' => $event->id,
        'contact_id' => $contact->id,
        'voucher_id' => '1234567890',
        'status' => Voucher::STATUS_ACTIVE,
        'creation_date' => now(),
        'expiry_date' => now()->addDays(5),
    ]);

    $this->actingAs($user)
        ->post(route('admin.scan.store'), [
            'voucher_id' => '1234567890',
        ])
        ->assertRedirect()
        ->assertSessionHas('scan_success');

    $this->assertDatabaseHas('vouchers', [
        'id' => $voucher->id,
        'status' => Voucher::STATUS_REDEEMED,
    ]);
});

it('rejects an invalid barcode', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('admin.scan.store'), [
            'voucher_id' => 'DOES_NOT_EXIST',
        ])
        ->assertRedirect()
        ->assertSessionHas('scan_error', 'Invalid barcode. Voucher not found.');
});

it('rejects an already redeemed voucher', function () {
    $user = User::factory()->create();

    $event = Event::create([
        'name' => 'Test Event',
        'slug' => 'test-event-2',
    ]);

    $voucher = Voucher::create([
        'event_id' => $event->id,
        'voucher_id' => 'USED_CODE',
        'status' => Voucher::STATUS_REDEEMED,
        'creation_date' => now(),
        'redeemed_at' => now()->subDay(),
    ]);

    $this->actingAs($user)
        ->post(route('admin.scan.store'), [
            'voucher_id' => 'USED_CODE',
        ])
        ->assertRedirect()
        ->assertSessionHas('scan_error');

    $this->assertDatabaseHas('vouchers', [
        'id' => $voucher->id,
        'status' => Voucher::STATUS_REDEEMED,
    ]);
});

it('rejects an expired voucher', function () {
    $user = User::factory()->create();

    $event = Event::create([
        'name' => 'Test Event',
        'slug' => 'test-event-3',
    ]);

    $voucher = Voucher::create([
        'event_id' => $event->id,
        'voucher_id' => 'EXPIRED_CODE',
        'status' => Voucher::STATUS_ACTIVE, // Status might still be active but date is past
        'creation_date' => now()->subDays(10),
        'expiry_date' => now()->subDay(),
    ]);

    $this->actingAs($user)
        ->post(route('admin.scan.store'), [
            'voucher_id' => 'EXPIRED_CODE',
        ])
        ->assertRedirect()
        ->assertSessionHas('scan_error');

    $this->assertDatabaseHas('vouchers', [
        'id' => $voucher->id,
        'status' => Voucher::STATUS_ACTIVE, // Should remain untouched
    ]);
});
