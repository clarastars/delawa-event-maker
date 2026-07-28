<?php

use App\Models\Contact;
use App\Models\Voucher;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

test('sync command updates remaining balance for all vouchers', function () {
    config([
        'services.tsepass.api_url' => 'https://api.tsepass.test',
        'services.tsepass.api_key' => 'secret',
    ]);

    Http::fake([
        'https://api.tsepass.test/queries/gift-card-simple*' => Http::sequence()
            ->push([
                'success' => true,
                'data' => [['netCardValue' => 150]],
            ])
            ->push([
                'success' => true,
                'data' => [['netCardValue' => 42]],
            ])
            ->push([
                'success' => true,
                'data' => [['netCardValue' => 75.5]],
            ]),
    ]);

    $activatedContact = Contact::create([
        'name' => 'Sara Ahmed',
        'phone' => '0551234567',
        'phone_normalized' => Contact::normalizePhone('0551234567'),
        'activated_at' => now(),
    ]);

    $activatedVoucher = Voucher::create([
        'voucher_id' => 'EG-SA-100',
        'creation_date' => now()->toDateString(),
        'balance' => 200,
        'status' => Voucher::STATUS_ACTIVE,
        'one_time_redemption' => true,
        'contact_id' => $activatedContact->id,
    ]);

    $notActivatedContact = Contact::create([
        'name' => 'Omar Ali',
        'phone' => '0559876543',
        'phone_normalized' => Contact::normalizePhone('0559876543'),
    ]);

    $notActivatedVoucher = Voucher::create([
        'voucher_id' => 'EG-SA-200',
        'creation_date' => now()->toDateString(),
        'balance' => 100,
        'status' => Voucher::STATUS_ACTIVE,
        'one_time_redemption' => true,
        'contact_id' => $notActivatedContact->id,
    ]);

    Contact::create([
        'name' => 'No Voucher',
        'phone' => '0551111111',
        'phone_normalized' => Contact::normalizePhone('0551111111'),
        'activated_at' => now(),
    ]);

    $secondActivatedContact = Contact::create([
        'name' => 'Layla Hassan',
        'phone' => '0552222222',
        'phone_normalized' => Contact::normalizePhone('0552222222'),
        'activated_at' => now()->subHour(),
    ]);

    $secondActivatedVoucher = Voucher::create([
        'voucher_id' => 'EG-SA-300',
        'creation_date' => now()->toDateString(),
        'balance' => 300,
        'status' => Voucher::STATUS_ACTIVE,
        'one_time_redemption' => true,
        'contact_id' => $secondActivatedContact->id,
    ]);

    $this->artisan('tsepass:sync-activated-balances')
        ->assertSuccessful();

    expect($activatedVoucher->fresh())
        ->remaining_balance->toEqual(150.0)
        ->remaining_balance_synced_at->not->toBeNull();

    expect($notActivatedVoucher->fresh())
        ->remaining_balance->toEqual(42.0)
        ->remaining_balance_synced_at->not->toBeNull();

    expect($secondActivatedVoucher->fresh())
        ->remaining_balance->toEqual(75.5)
        ->remaining_balance_synced_at->not->toBeNull();

    Http::assertSentCount(3);
});

test('sync command updates unassigned vouchers', function () {
    config([
        'services.tsepass.api_url' => 'https://api.tsepass.test',
        'services.tsepass.api_key' => 'secret',
    ]);

    Http::fake([
        'https://api.tsepass.test/queries/gift-card-simple*' => Http::response([
            'success' => true,
            'data' => [['netCardValue' => 150]],
        ]),
    ]);

    Voucher::create([
        'voucher_id' => 'EG-POOL-100',
        'creation_date' => now()->toDateString(),
        'balance' => 200,
        'status' => Voucher::STATUS_ACTIVE,
        'one_time_redemption' => true,
    ]);

    $this->artisan('tsepass:sync-activated-balances')
        ->assertSuccessful();

    expect(Voucher::query()->where('voucher_id', 'EG-POOL-100')->first())
        ->remaining_balance->toEqual(150.0);

    Http::assertSentCount(1);
});

test('sync command updates remaining balance for all vouchers on an activated contact', function () {
    config([
        'services.tsepass.api_url' => 'https://api.tsepass.test',
        'services.tsepass.api_key' => 'secret',
    ]);

    Http::fake([
        'https://api.tsepass.test/queries/gift-card-simple*' => Http::sequence()
            ->push([
                'success' => true,
                'data' => [['netCardValue' => 150]],
            ])
            ->push([
                'success' => true,
                'data' => [['netCardValue' => 34]],
            ]),
    ]);

    $contact = Contact::create([
        'name' => 'Sara Ahmed',
        'phone' => '0551234567',
        'phone_normalized' => Contact::normalizePhone('0551234567'),
        'activated_at' => now(),
    ]);

    $firstVoucher = Voucher::create([
        'voucher_id' => 'EG-SA-100',
        'creation_date' => now()->toDateString(),
        'balance' => 200,
        'status' => Voucher::STATUS_ACTIVE,
        'one_time_redemption' => true,
        'contact_id' => $contact->id,
    ]);

    $secondVoucher = Voucher::create([
        'voucher_id' => 'EG-SA-101',
        'creation_date' => now()->toDateString(),
        'balance' => 34,
        'status' => Voucher::STATUS_ACTIVE,
        'one_time_redemption' => true,
        'contact_id' => $contact->id,
    ]);

    $this->artisan('tsepass:sync-activated-balances')
        ->assertSuccessful();

    expect($firstVoucher->fresh())
        ->remaining_balance->toEqual(150.0)
        ->remaining_balance_synced_at->not->toBeNull();

    expect($secondVoucher->fresh())
        ->remaining_balance->toEqual(34.0)
        ->remaining_balance_synced_at->not->toBeNull();

    Http::assertSentCount(2);
});

test('sync command reports failure when api lookup fails', function () {
    config([
        'services.tsepass.api_url' => 'https://api.tsepass.test',
        'services.tsepass.api_key' => 'secret',
    ]);

    Http::fake([
        'https://api.tsepass.test/queries/gift-card-simple*' => Http::response([], 500),
    ]);

    $contact = Contact::create([
        'name' => 'Sara Ahmed',
        'phone' => '0551234567',
        'phone_normalized' => Contact::normalizePhone('0551234567'),
        'activated_at' => now(),
    ]);

    $voucher = Voucher::create([
        'voucher_id' => 'EG-SA-100',
        'creation_date' => now()->toDateString(),
        'balance' => 200,
        'status' => Voucher::STATUS_ACTIVE,
        'one_time_redemption' => true,
        'contact_id' => $contact->id,
    ]);

    $this->artisan('tsepass:sync-activated-balances')
        ->assertSuccessful()
        ->expectsOutputToContain('Failed to fetch balance for EG-SA-100');

    expect($voucher->fresh()->remaining_balance)->toBeNull();
});

test('sync command is scheduled every thirty minutes', function () {
    $schedule = app(Schedule::class);

    $event = collect($schedule->events())->first(
        fn ($event) => str_contains($event->command ?? '', 'tsepass:sync-activated-balances')
    );

    expect($event)->not->toBeNull();
    expect($event->expression)->toBe('*/30 * * * *');
});
