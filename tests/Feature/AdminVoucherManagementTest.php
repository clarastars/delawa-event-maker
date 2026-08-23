<?php

use App\Models\Contact;
use App\Models\Event;
use App\Models\User;
use App\Models\Voucher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;

uses(RefreshDatabase::class);

test('admin can create a voucher', function () {
    $admin = User::factory()->create();
    $event = Event::factory()->create();

    $this->actingAs($admin)
        ->post(route('admin.vouchers.store'), [
            'event_id' => $event->id,
            'voucher_id' => 'EG-SA-001',
            'creation_date' => now()->toDateString(),
            'expiry_date' => now()->addMonth()->toDateString(),
            'balance' => 150,
            'status' => Voucher::STATUS_ACTIVE,
            'one_time_redemption' => '1',
        ])
        ->assertRedirect(route('admin.vouchers.index'));

    $this->assertDatabaseHas('vouchers', [
        'voucher_id' => 'EG-SA-001',
        'event_id' => $event->id,
        'status' => Voucher::STATUS_ACTIVE,
    ]);
});

test('creating a voucher requires an event', function () {
    $admin = User::factory()->create();

    $this->actingAs($admin)
        ->post(route('admin.vouchers.store'), [
            'voucher_id' => 'EG-SA-001',
            'creation_date' => now()->toDateString(),
            'balance' => 150,
            'status' => Voucher::STATUS_ACTIVE,
        ])
        ->assertSessionHasErrors('event_id');
});

test('admin can upload contacts and grant them entries for an event', function () {
    $admin = User::factory()->create();
    $event = Event::factory()->create();

    $file = UploadedFile::fake()->createWithContent(
        'contacts.csv',
        "name,email,phone\nSara,sara@example.com,+966551234567\n"
    );

    $this->actingAs($admin)
        ->post(route('admin.contacts.upload.store'), [
            'contacts' => $file,
            'event_id' => $event->id,
            'assignment_type' => 'entries',
            'entries' => 2,
        ])
        ->assertRedirect(route('admin.contacts.index'));

    $contact = Contact::firstWhere('phone_normalized', Contact::normalizePhone('+966551234567'));

    expect($contact)->not->toBeNull();

    $this->assertDatabaseHas('contact_event', [
        'contact_id' => $contact->id,
        'event_id' => $event->id,
        'entries' => 2,
    ]);
});

test('admin can upload contacts without assigning event access', function () {
    $admin = User::factory()->create();
    $event = Event::factory()->create();

    $file = UploadedFile::fake()->createWithContent(
        'contacts.csv',
        "name,email,phone\nSara,sara@example.com,+966551234567\n"
    );

    $this->actingAs($admin)
        ->post(route('admin.contacts.upload.store'), [
            'contacts' => $file,
        ])
        ->assertRedirect(route('admin.contacts.index'));

    $contact = Contact::firstWhere('phone_normalized', Contact::normalizePhone('+966551234567'));

    expect($contact)->not->toBeNull();

    $this->assertDatabaseMissing('contact_event', [
        'contact_id' => $contact->id,
    ]);
});

test('admin can add a contact via form and grant them entries for an event', function () {
    $admin = User::factory()->create();
    $event = Event::factory()->create();

    $this->actingAs($admin)
        ->post(route('admin.contacts.store'), [
            'name' => 'Ahmed',
            'email' => 'ahmed@example.com',
            'phone' => '0559876543',
            'event_id' => $event->id,
            'assignment_type' => 'entries',
            'entries' => 3,
        ])
        ->assertRedirect(route('admin.contacts.upload.create'))
        ->assertSessionHas('status');

    $contact = Contact::firstWhere('phone_normalized', Contact::normalizePhone('0559876543'));

    expect($contact)->not->toBeNull()
        ->name->toBe('Ahmed');

    $this->assertDatabaseHas('contact_event', [
        'contact_id' => $contact->id,
        'event_id' => $event->id,
        'entries' => 3,
    ]);
});

test('admin can auto-assign multiple general vouchers matching entries count', function () {
    $admin = User::factory()->create();
    $event = Event::factory()->create();

    foreach (['AUTO-001', 'AUTO-002', 'AUTO-003'] as $voucherId) {
        Voucher::create([
            'event_id' => $event->id,
            'voucher_id' => $voucherId,
            'creation_date' => now()->toDateString(),
            'balance' => 100,
            'status' => Voucher::STATUS_ACTIVE,
            'one_time_redemption' => true,
        ]);
    }

    $this->actingAs($admin)
        ->post(route('admin.contacts.store'), [
            'name' => 'Sara',
            'email' => 'sara@example.com',
            'phone' => '0551112233',
            'event_id' => $event->id,
            'assignment_type' => 'auto_assign',
            'entries' => 2,
        ])
        ->assertRedirect(route('admin.contacts.upload.create'))
        ->assertSessionHas('status');

    $contact = Contact::firstWhere('phone_normalized', Contact::normalizePhone('0551112233'));

    expect($contact)->not->toBeNull()
        ->and($contact->vouchers()->where('event_id', $event->id)->count())->toBe(2)
        ->and(Voucher::query()->whereNull('contact_id')->where('event_id', $event->id)->count())->toBe(1);
});

test('admin can upload contacts and auto-assign vouchers matching entries count', function () {
    $admin = User::factory()->create();
    $event = Event::factory()->create();

    foreach (['UP-001', 'UP-002', 'UP-003', 'UP-004'] as $voucherId) {
        Voucher::create([
            'event_id' => $event->id,
            'voucher_id' => $voucherId,
            'creation_date' => now()->toDateString(),
            'balance' => 100,
            'status' => Voucher::STATUS_ACTIVE,
            'one_time_redemption' => true,
        ]);
    }

    $file = UploadedFile::fake()->createWithContent(
        'contacts.csv',
        "name,email,phone\nSara,sara@example.com,+966551234567\nOmar,,0559876543\n"
    );

    $this->actingAs($admin)
        ->post(route('admin.contacts.upload.store'), [
            'contacts' => $file,
            'event_id' => $event->id,
            'assignment_type' => 'auto_assign',
            'entries' => 2,
        ])
        ->assertRedirect(route('admin.contacts.index'));

    $sara = Contact::firstWhere('phone_normalized', Contact::normalizePhone('+966551234567'));
    $omar = Contact::firstWhere('phone_normalized', Contact::normalizePhone('0559876543'));

    expect($sara->vouchers()->where('event_id', $event->id)->count())->toBe(2)
        ->and($omar->vouchers()->where('event_id', $event->id)->count())->toBe(2);
});

test('admin can upload vouchers from tsv file into an event', function () {
    $admin = User::factory()->create();
    $event = Event::factory()->create();

    $file = UploadedFile::fake()->createWithContent(
        'vouchers.tsv',
        "entryId\tbalance\tcurrencyCode\tActiveFrom\tExpiryDate\tStatus\tOneTimeRedemption\n".
        "101229318\t400\tSAR\t5/15/26\t6/1/2026\t2\t0\n"
    );

    $this->actingAs($admin)
        ->post(route('admin.vouchers.upload.store'), [
            'vouchers' => $file,
            'event_id' => $event->id,
        ])
        ->assertRedirect(route('admin.events.show', $event));

    $this->assertDatabaseHas('vouchers', [
        'voucher_id' => '101229318',
        'event_id' => $event->id,
        'balance' => 400,
        'status' => Voucher::STATUS_ACTIVE,
        'one_time_redemption' => false,
    ]);
});

test('admin can download voucher upload sample file', function () {
    $admin = User::factory()->create();

    $this->actingAs($admin)
        ->get(route('admin.vouchers.upload.sample'))
        ->assertSuccessful()
        ->assertHeader('content-disposition')
        ->assertSee('entryId');
});

test('admin can filter vouchers by event', function () {
    $admin = User::factory()->create();
    $event = Event::factory()->create();
    $otherEvent = Event::factory()->create();

    Voucher::create([
        'event_id' => $event->id,
        'voucher_id' => 'EVENT-A-001',
        'creation_date' => now()->toDateString(),
        'balance' => 100,
        'status' => Voucher::STATUS_ACTIVE,
        'one_time_redemption' => true,
    ]);

    Voucher::create([
        'event_id' => $otherEvent->id,
        'voucher_id' => 'EVENT-B-001',
        'creation_date' => now()->toDateString(),
        'balance' => 100,
        'status' => Voucher::STATUS_ACTIVE,
        'one_time_redemption' => true,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.vouchers.index', ['event' => $event->id]))
        ->assertSuccessful()
        ->assertSee('EVENT-A-001')
        ->assertDontSee('EVENT-B-001');
});
