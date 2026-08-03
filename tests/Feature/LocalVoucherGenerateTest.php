<?php

use App\Contracts\Otp;
use App\Models\Contact;
use App\Models\Event;
use App\Models\Product;
use App\Models\User;
use App\Models\Voucher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

test('admin can generate local vouchers for an event product', function () {
    $admin = User::factory()->create();
    $event = Event::factory()->create();
    $product = Product::factory()->create(['event_id' => $event->id, 'name' => 'Gold']);

    $this->actingAs($admin)
        ->post(route('admin.vouchers.generate.store'), [
            'event_id' => $event->id,
            'product_id' => $product->id,
            'quantity' => 3,
            'balance' => 25,
            'expiry_date' => now()->addMonth()->toDateString(),
            'one_time_redemption' => '1',
        ])
        ->assertRedirect(route('admin.events.show', $event))
        ->assertSessionHas('status');

    expect(Voucher::query()->where('event_id', $event->id)->count())->toBe(3);

    $this->assertDatabaseHas('vouchers', [
        'event_id' => $event->id,
        'product_id' => $product->id,
        'source' => Voucher::SOURCE_LOCAL,
        'balance' => 25,
        'remaining_balance' => 25,
        'status' => Voucher::STATUS_ACTIVE,
    ]);
});

test('generate form rejects product from another event', function () {
    $admin = User::factory()->create();
    $event = Event::factory()->create();
    $otherEvent = Event::factory()->create();
    $product = Product::factory()->create(['event_id' => $otherEvent->id]);

    $this->actingAs($admin)
        ->post(route('admin.vouchers.generate.store'), [
            'event_id' => $event->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'balance' => 18,
        ])
        ->assertSessionHasErrors('product_id');
});

test('sync command skips local vouchers', function () {
    config([
        'services.tsepass.api_url' => 'https://api.tsepass.test',
        'services.tsepass.api_key' => 'secret',
    ]);

    Http::fake([
        'https://api.tsepass.test/queries/gift-card-simple*' => Http::response([
            'success' => true,
            'data' => [['netCardValue' => 99]],
        ]),
    ]);

    $tsepass = Voucher::create([
        'voucher_id' => 'EG-SA-100',
        'source' => Voucher::SOURCE_TSEPASS,
        'creation_date' => now()->toDateString(),
        'balance' => 200,
        'status' => Voucher::STATUS_ACTIVE,
        'one_time_redemption' => true,
    ]);

    $local = Voucher::create([
        'voucher_id' => 'DLWLOCAL00001',
        'source' => Voucher::SOURCE_LOCAL,
        'creation_date' => now()->toDateString(),
        'balance' => 25,
        'remaining_balance' => 25,
        'status' => Voucher::STATUS_ACTIVE,
        'one_time_redemption' => true,
    ]);

    $this->artisan('tsepass:sync-activated-balances')
        ->assertSuccessful();

    expect($tsepass->fresh()->remaining_balance)->toEqual(99.0);
    expect($local->fresh()->remaining_balance)->toEqual(25.0);

    Http::assertSentCount(1);
});

test('guest vouchers page uses stored remaining balance for local vouchers without calling tsepass', function () {
    config([
        'services.tsepass.api_url' => 'https://api.tsepass.test',
        'services.tsepass.api_key' => 'secret',
    ]);

    Http::fake();

    $this->mock(Otp::class, function ($mock): void {
        $mock->shouldReceive('send')->once()->with('+966551234567');
        $mock->shouldReceive('verify')->once()->with('+966551234567', '1234')->andReturn(true);
    });

    $event = Event::factory()->create();
    $contact = Contact::create([
        'name' => 'Sara',
        'phone' => '+966 55 123 4567',
        'phone_normalized' => Contact::normalizePhone('+966 55 123 4567'),
    ]);

    Voucher::create([
        'contact_id' => $contact->id,
        'event_id' => $event->id,
        'source' => Voucher::SOURCE_LOCAL,
        'voucher_id' => 'DLWLOCALGUEST1',
        'creation_date' => now()->toDateString(),
        'expiry_date' => now()->addMonth()->toDateString(),
        'balance' => 25,
        'remaining_balance' => 25,
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
        ->assertSee('DLWLOCALGUEST1')
        ->assertSee('25.00');

    Http::assertNothingSent();
});
