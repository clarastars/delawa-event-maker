<?php

use App\Models\Contact;
use App\Models\Event;
use App\Models\User;
use App\Models\Voucher;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('admin can download contacts upload sample file', function () {
    $admin = User::factory()->create();

    $this->actingAs($admin)
        ->get(route('admin.contacts.upload.sample'))
        ->assertSuccessful()
        ->assertHeader('content-disposition')
        ->assertSee('name,email,phone');
});

test('admin can export contacts as csv', function () {
    $admin = User::factory()->create();

    $contact = Contact::create([
        'name' => 'Sara Ahmed',
        'email' => 'sara@example.com',
        'phone' => '0551234567',
        'phone_normalized' => Contact::normalizePhone('0551234567'),
        'activated_at' => now(),
    ]);

    $voucher = Voucher::create([
        'voucher_id' => 'EG-SA-100',
        'creation_date' => now()->toDateString(),
        'balance' => 200,
        'remaining_balance' => 150,
        'remaining_balance_synced_at' => now(),
        'status' => Voucher::STATUS_ACTIVE,
        'one_time_redemption' => true,
        'contact_id' => $contact->id,
    ]);

    Contact::create([
        'name' => 'Omar Ali',
        'email' => 'omar@example.com',
        'phone' => '0559876543',
        'phone_normalized' => Contact::normalizePhone('0559876543'),
    ]);

    $export = $this->actingAs($admin)
        ->get(route('admin.contacts.export'));

    $export->assertSuccessful()
        ->assertHeader('content-disposition');

    $csv = $export->streamedContent();

    expect($csv)
        ->toContain('name,email,phone,voucher_ids,activated_at,card_value,remaining_balance')
        ->toContain('Sara Ahmed')
        ->toContain('sara@example.com')
        ->toContain('0551234567')
        ->toContain($voucher->voucher_id)
        ->toContain('200.00')
        ->toContain('150.00')
        ->toContain('Omar Ali')
        ->toContain('omar@example.com')
        ->toContain('0559876543');

    $filteredExport = $this->actingAs($admin)
        ->get(route('admin.contacts.export', ['search' => 'Sara']));

    $filteredExport->assertSuccessful();

    $filteredCsv = $filteredExport->streamedContent();

    expect($filteredCsv)
        ->toContain('Sara Ahmed')
        ->not->toContain('Omar Ali');
});

test('contacts export lists all vouchers of a contact', function () {
    $admin = User::factory()->create();

    $contact = Contact::create([
        'name' => 'Sara Ahmed',
        'phone' => '0551234567',
        'phone_normalized' => Contact::normalizePhone('0551234567'),
        'activated_at' => now(),
    ]);

    foreach (['EG-SA-100', 'EG-SA-101'] as $voucherId) {
        Voucher::create([
            'voucher_id' => $voucherId,
            'creation_date' => now()->toDateString(),
            'balance' => 200,
            'status' => Voucher::STATUS_ACTIVE,
            'one_time_redemption' => true,
            'contact_id' => $contact->id,
        ]);
    }

    $csv = $this->actingAs($admin)
        ->get(route('admin.contacts.export'))
        ->streamedContent();

    expect($csv)
        ->toContain('EG-SA-100 | EG-SA-101')
        ->toContain('400.00');
});

test('admin contact show page displays card value and remaining balance for activated contacts', function () {
    $admin = User::factory()->create();

    $contact = Contact::create([
        'name' => 'Sara Ahmed',
        'email' => 'sara@example.com',
        'phone' => '0551234567',
        'phone_normalized' => Contact::normalizePhone('0551234567'),
        'activated_at' => now(),
    ]);

    Voucher::create([
        'voucher_id' => 'EG-SA-100',
        'creation_date' => now()->toDateString(),
        'balance' => 200,
        'remaining_balance' => 150,
        'remaining_balance_synced_at' => now(),
        'status' => Voucher::STATUS_ACTIVE,
        'one_time_redemption' => true,
        'contact_id' => $contact->id,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.contacts.show', $contact))
        ->assertSuccessful()
        ->assertSee('Card value')
        ->assertSee('Remaining')
        ->assertSee('200.00')
        ->assertSee('150.00');
});

test('admin contact show page shows not synced when remaining balance is missing', function () {
    $admin = User::factory()->create();

    $contact = Contact::create([
        'name' => 'Sara Ahmed',
        'phone' => '0551234567',
        'phone_normalized' => Contact::normalizePhone('0551234567'),
        'activated_at' => now(),
    ]);

    Voucher::create([
        'voucher_id' => 'EG-SA-100',
        'creation_date' => now()->toDateString(),
        'balance' => 200,
        'status' => Voucher::STATUS_ACTIVE,
        'one_time_redemption' => true,
        'contact_id' => $contact->id,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.contacts.show', $contact))
        ->assertSuccessful()
        ->assertSee('Not synced');
});

test('admin contacts index shows color coded voucher balance crumbs for non activated contacts', function () {
    $admin = User::factory()->create();

    $contact = Contact::create([
        'name' => 'Omar Ali',
        'phone' => '0559876543',
        'phone_normalized' => Contact::normalizePhone('0559876543'),
    ]);

    Voucher::create([
        'voucher_id' => 'EG-PART-200',
        'creation_date' => now()->toDateString(),
        'balance' => 34,
        'remaining_balance' => 3,
        'remaining_balance_synced_at' => now(),
        'status' => Voucher::STATUS_ACTIVE,
        'one_time_redemption' => true,
        'contact_id' => $contact->id,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.contacts.index'))
        ->assertSuccessful()
        ->assertSee('EG-PART-200 · 3.00 SR', false)
        ->assertSee('bg-amber-50', false);
});

test('admin contacts index shows color coded voucher balance crumbs', function () {
    $admin = User::factory()->create();

    $contact = Contact::create([
        'name' => 'Sara Ahmed',
        'email' => 'sara@example.com',
        'phone' => '0551234567',
        'phone_normalized' => Contact::normalizePhone('0551234567'),
        'activated_at' => now(),
    ]);

    Voucher::create([
        'voucher_id' => 'EG-FULL-100',
        'creation_date' => now()->toDateString(),
        'balance' => 34,
        'remaining_balance' => 34,
        'remaining_balance_synced_at' => now(),
        'status' => Voucher::STATUS_ACTIVE,
        'one_time_redemption' => true,
        'contact_id' => $contact->id,
    ]);

    Voucher::create([
        'voucher_id' => 'EG-PART-100',
        'creation_date' => now()->toDateString(),
        'balance' => 34,
        'remaining_balance' => 3,
        'remaining_balance_synced_at' => now(),
        'status' => Voucher::STATUS_ACTIVE,
        'one_time_redemption' => true,
        'contact_id' => $contact->id,
    ]);

    Voucher::create([
        'voucher_id' => 'EG-USED-100',
        'creation_date' => now()->toDateString(),
        'balance' => 34,
        'remaining_balance' => 0,
        'remaining_balance_synced_at' => now(),
        'status' => Voucher::STATUS_ACTIVE,
        'one_time_redemption' => true,
        'contact_id' => $contact->id,
    ]);

    $response = $this->actingAs($admin)
        ->get(route('admin.contacts.index'));

    $response->assertSuccessful()
        ->assertSee('EG-FULL-100 · 34.00 SR', false)
        ->assertSee('EG-PART-100 · 3.00 SR', false)
        ->assertSee('EG-USED-100 · 0.00 SR', false)
        ->assertSee('bg-emerald-50', false)
        ->assertSee('bg-amber-50', false)
        ->assertSee('bg-red-50', false);
});

test('admin contacts index shows card value and remaining balance for activated contacts', function () {
    $admin = User::factory()->create();

    $contact = Contact::create([
        'name' => 'Sara Ahmed',
        'email' => 'sara@example.com',
        'phone' => '0551234567',
        'phone_normalized' => Contact::normalizePhone('0551234567'),
        'activated_at' => now(),
    ]);

    Voucher::create([
        'voucher_id' => 'EG-SA-100',
        'creation_date' => now()->toDateString(),
        'balance' => 200,
        'remaining_balance' => 150,
        'remaining_balance_synced_at' => now(),
        'status' => Voucher::STATUS_ACTIVE,
        'one_time_redemption' => true,
        'contact_id' => $contact->id,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.contacts.index'))
        ->assertSuccessful()
        ->assertSee('200.00')
        ->assertSee('150.00');
});

test('admin can list and search contacts', function () {
    $admin = User::factory()->create();

    Contact::create([
        'name' => 'Sara Ahmed',
        'email' => 'sara@example.com',
        'phone' => '0551234567',
        'phone_normalized' => Contact::normalizePhone('0551234567'),
    ]);

    Contact::create([
        'name' => 'Omar Ali',
        'email' => 'omar@example.com',
        'phone' => '0559876543',
        'phone_normalized' => Contact::normalizePhone('0559876543'),
    ]);

    $this->actingAs($admin)
        ->get(route('admin.contacts.index'))
        ->assertSuccessful()
        ->assertSee('Sara Ahmed')
        ->assertSee('Omar Ali');

    $this->actingAs($admin)
        ->get(route('admin.contacts.index', ['search' => 'Sara']))
        ->assertSuccessful()
        ->assertSee('Sara Ahmed')
        ->assertDontSee('Omar Ali');
});

test('admin contacts index shows number of entries for each contact', function () {
    $admin = User::factory()->create();
    $event = Event::factory()->create();

    $withEntries = Contact::create([
        'name' => 'Sara Ahmed',
        'email' => 'sara@example.com',
        'phone' => '0551234567',
        'phone_normalized' => Contact::normalizePhone('0551234567'),
    ]);
    $withEntries->events()->attach($event, ['entries' => 3]);

    Contact::create([
        'name' => 'Omar Ali',
        'email' => 'omar@example.com',
        'phone' => '0559876543',
        'phone_normalized' => Contact::normalizePhone('0559876543'),
    ]);

    $this->actingAs($admin)
        ->get(route('admin.contacts.index'))
        ->assertSuccessful()
        ->assertSee('Entries')
        ->assertSee('3')
        ->assertSee('—');
});

test('admin contact show page hides vouchers from closed events in assign dropdown', function () {
    $admin = User::factory()->create();

    $openEvent = Event::factory()->create(['name' => 'Open Campaign']);
    $closedEvent = Event::factory()->closed()->create(['name' => 'Closed Campaign']);

    $contact = Contact::create([
        'name' => 'Sara Ahmed',
        'phone' => '0551234567',
        'phone_normalized' => Contact::normalizePhone('0551234567'),
    ]);

    Voucher::create([
        'event_id' => $openEvent->id,
        'voucher_id' => 'EG-OPEN-100',
        'creation_date' => now()->toDateString(),
        'balance' => 200,
        'status' => Voucher::STATUS_ACTIVE,
        'one_time_redemption' => true,
    ]);

    Voucher::create([
        'event_id' => $closedEvent->id,
        'voucher_id' => 'EG-CLOSED-100',
        'creation_date' => now()->toDateString(),
        'balance' => 200,
        'status' => Voucher::STATUS_ACTIVE,
        'one_time_redemption' => true,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.contacts.show', $contact))
        ->assertSuccessful()
        ->assertSee('EG-OPEN-100')
        ->assertSee('Open Campaign')
        ->assertDontSee('EG-CLOSED-100')
        ->assertDontSee('Closed Campaign');
});

test('admin cannot assign a voucher from a closed event', function () {
    $admin = User::factory()->create();
    $closedEvent = Event::factory()->closed()->create();

    $contact = Contact::create([
        'name' => 'Sara Ahmed',
        'phone' => '0551234567',
        'phone_normalized' => Contact::normalizePhone('0551234567'),
    ]);

    $voucher = Voucher::create([
        'event_id' => $closedEvent->id,
        'voucher_id' => 'EG-CLOSED-100',
        'creation_date' => now()->toDateString(),
        'balance' => 200,
        'status' => Voucher::STATUS_ACTIVE,
        'one_time_redemption' => true,
    ]);

    $this->actingAs($admin)
        ->from(route('admin.contacts.show', $contact))
        ->post(route('admin.contacts.assign-voucher', $contact), [
            'voucher_id' => $voucher->id,
        ])
        ->assertRedirect(route('admin.contacts.show', $contact))
        ->assertSessionHasErrors('voucher_id');

    $this->assertDatabaseHas('vouchers', [
        'id' => $voucher->id,
        'contact_id' => null,
    ]);
});

test('admin can view a contact and assign a voucher', function () {
    $admin = User::factory()->create();
    $event = Event::factory()->create();

    $contact = Contact::create([
        'name' => 'Sara Ahmed',
        'email' => 'sara@example.com',
        'phone' => '0551234567',
        'phone_normalized' => Contact::normalizePhone('0551234567'),
    ]);

    $voucher = Voucher::create([
        'event_id' => $event->id,
        'voucher_id' => 'EG-SA-100',
        'creation_date' => now()->toDateString(),
        'expiry_date' => now()->addMonth()->toDateString(),
        'balance' => 200,
        'status' => Voucher::STATUS_ACTIVE,
        'one_time_redemption' => true,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.contacts.show', $contact))
        ->assertSuccessful()
        ->assertSee('Sara Ahmed')
        ->assertSee('No vouchers assigned yet')
        ->assertSee('EG-SA-100');

    $this->actingAs($admin)
        ->post(route('admin.contacts.assign-voucher', $contact), [
            'voucher_id' => $voucher->id,
        ])
        ->assertRedirect(route('admin.contacts.show', $contact))
        ->assertSessionHas('status');

    $this->assertDatabaseHas('vouchers', [
        'id' => $voucher->id,
        'contact_id' => $contact->id,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.contacts.show', $contact))
        ->assertSuccessful()
        ->assertSee('EG-SA-100')
        ->assertSee($event->name)
        ->assertSee('Unassign');
});

test('admin can assign multiple vouchers to one contact', function () {
    $admin = User::factory()->create();
    $event = Event::factory()->create();

    $contact = Contact::create([
        'name' => 'Sara Ahmed',
        'phone' => '0551234567',
        'phone_normalized' => Contact::normalizePhone('0551234567'),
    ]);

    Voucher::create([
        'event_id' => $event->id,
        'voucher_id' => 'EG-SA-001',
        'creation_date' => now()->toDateString(),
        'balance' => 100,
        'status' => Voucher::STATUS_ACTIVE,
        'one_time_redemption' => true,
        'contact_id' => $contact->id,
    ]);

    $secondVoucher = Voucher::create([
        'event_id' => $event->id,
        'voucher_id' => 'EG-SA-002',
        'creation_date' => now()->toDateString(),
        'balance' => 100,
        'status' => Voucher::STATUS_ACTIVE,
        'one_time_redemption' => true,
    ]);

    $this->actingAs($admin)
        ->post(route('admin.contacts.assign-voucher', $contact), [
            'voucher_id' => $secondVoucher->id,
        ])
        ->assertRedirect(route('admin.contacts.show', $contact))
        ->assertSessionHas('status');

    expect($contact->fresh()->vouchers)->toHaveCount(2);
});

test('admin can unassign a voucher from a contact', function () {
    $admin = User::factory()->create();

    $contact = Contact::create([
        'name' => 'Sara Ahmed',
        'phone' => '0551234567',
        'phone_normalized' => Contact::normalizePhone('0551234567'),
    ]);

    $voucher = Voucher::create([
        'voucher_id' => 'EG-SA-100',
        'creation_date' => now()->toDateString(),
        'balance' => 200,
        'status' => Voucher::STATUS_ACTIVE,
        'one_time_redemption' => true,
        'contact_id' => $contact->id,
    ]);

    $this->actingAs($admin)
        ->delete(route('admin.contacts.unassign-voucher', [$contact, $voucher]))
        ->assertRedirect(route('admin.contacts.show', $contact))
        ->assertSessionHas('status');

    $this->assertDatabaseHas('vouchers', [
        'id' => $voucher->id,
        'contact_id' => null,
    ]);
});

test('admin cannot unassign a voucher that belongs to another contact', function () {
    $admin = User::factory()->create();

    $contact = Contact::create([
        'name' => 'Sara Ahmed',
        'phone' => '0551234567',
        'phone_normalized' => Contact::normalizePhone('0551234567'),
    ]);

    $otherContact = Contact::create([
        'name' => 'Omar Ali',
        'phone' => '0559876543',
        'phone_normalized' => Contact::normalizePhone('0559876543'),
    ]);

    $voucher = Voucher::create([
        'voucher_id' => 'EG-SA-100',
        'creation_date' => now()->toDateString(),
        'balance' => 200,
        'status' => Voucher::STATUS_ACTIVE,
        'one_time_redemption' => true,
        'contact_id' => $otherContact->id,
    ]);

    $this->actingAs($admin)
        ->delete(route('admin.contacts.unassign-voucher', [$contact, $voucher]))
        ->assertRedirect(route('admin.contacts.show', $contact))
        ->assertSessionHasErrors('voucher_id');

    $this->assertDatabaseHas('vouchers', [
        'id' => $voucher->id,
        'contact_id' => $otherContact->id,
    ]);
});

test('admin contact show page displays and allows updating event entries', function () {
    $admin = User::factory()->create();
    $event = Event::factory()->create(['name' => 'Ramadan Gifts']);

    $contact = Contact::create([
        'name' => 'Sara Ahmed',
        'phone' => '0551234567',
        'phone_normalized' => Contact::normalizePhone('0551234567'),
    ]);
    $contact->events()->attach($event, ['entries' => 2]);

    $this->actingAs($admin)
        ->get(route('admin.contacts.show', $contact))
        ->assertSuccessful()
        ->assertSee('Entries')
        ->assertSee('Ramadan Gifts')
        ->assertSee('value="2"', false);

    $this->actingAs($admin)
        ->put(route('admin.contacts.update-entries', [$contact, $event]), [
            'entries' => 5,
            'editing_event_id' => $event->id,
        ])
        ->assertRedirect(route('admin.contacts.show', $contact))
        ->assertSessionHas('status');

    $this->assertDatabaseHas('contact_event', [
        'contact_id' => $contact->id,
        'event_id' => $event->id,
        'entries' => 5,
    ]);
});

test('admin cannot set entries below vouchers already claimed for an event', function () {
    $admin = User::factory()->create();
    $event = Event::factory()->create();

    $contact = Contact::create([
        'name' => 'Sara Ahmed',
        'phone' => '0551234567',
        'phone_normalized' => Contact::normalizePhone('0551234567'),
    ]);
    $contact->events()->attach($event, ['entries' => 3]);

    Voucher::create([
        'voucher_id' => 'EG-SA-100',
        'creation_date' => now()->toDateString(),
        'balance' => 100,
        'status' => Voucher::STATUS_ACTIVE,
        'one_time_redemption' => true,
        'contact_id' => $contact->id,
        'event_id' => $event->id,
    ]);

    Voucher::create([
        'voucher_id' => 'EG-SA-101',
        'creation_date' => now()->toDateString(),
        'balance' => 100,
        'status' => Voucher::STATUS_ACTIVE,
        'one_time_redemption' => true,
        'contact_id' => $contact->id,
        'event_id' => $event->id,
    ]);

    $this->actingAs($admin)
        ->from(route('admin.contacts.show', $contact))
        ->put(route('admin.contacts.update-entries', [$contact, $event]), [
            'entries' => 1,
            'editing_event_id' => $event->id,
        ])
        ->assertRedirect(route('admin.contacts.show', $contact))
        ->assertSessionHasErrors('entries');

    $this->assertDatabaseHas('contact_event', [
        'contact_id' => $contact->id,
        'event_id' => $event->id,
        'entries' => 3,
    ]);
});

test('admin can update a contact phone number', function () {
    $admin = User::factory()->create();

    $contact = Contact::create([
        'name' => 'Sara Ahmed',
        'phone' => '0551234567',
        'phone_normalized' => Contact::normalizePhone('0551234567'),
    ]);

    $this->actingAs($admin)
        ->put(route('admin.contacts.update', $contact), [
            'phone' => '0559876543',
        ])
        ->assertRedirect(route('admin.contacts.show', $contact))
        ->assertSessionHas('status');

    $this->assertDatabaseHas('contacts', [
        'id' => $contact->id,
        'phone' => '0559876543',
        'phone_normalized' => Contact::normalizePhone('0559876543'),
    ]);
});

test('admin cannot update a contact phone to one already in use', function () {
    $admin = User::factory()->create();

    Contact::create([
        'name' => 'Omar Ali',
        'phone' => '0559876543',
        'phone_normalized' => Contact::normalizePhone('0559876543'),
    ]);

    $contact = Contact::create([
        'name' => 'Sara Ahmed',
        'phone' => '0551234567',
        'phone_normalized' => Contact::normalizePhone('0551234567'),
    ]);

    $this->actingAs($admin)
        ->from(route('admin.contacts.show', $contact))
        ->put(route('admin.contacts.update', $contact), [
            'phone' => '0559876543',
        ])
        ->assertRedirect(route('admin.contacts.show', $contact))
        ->assertSessionHasErrors('phone');

    $this->assertDatabaseHas('contacts', [
        'id' => $contact->id,
        'phone' => '0551234567',
        'phone_normalized' => Contact::normalizePhone('0551234567'),
    ]);
});

test('admin can delete a contact and unassign its vouchers', function () {
    $admin = User::factory()->create();

    $contact = Contact::create([
        'name' => 'Sara Ahmed',
        'phone' => '0551234567',
        'phone_normalized' => Contact::normalizePhone('0551234567'),
    ]);

    $voucher = Voucher::create([
        'voucher_id' => 'EG-SA-100',
        'creation_date' => now()->toDateString(),
        'balance' => 200,
        'status' => Voucher::STATUS_ACTIVE,
        'one_time_redemption' => true,
        'contact_id' => $contact->id,
    ]);

    $this->actingAs($admin)
        ->delete(route('admin.contacts.destroy', $contact))
        ->assertRedirect(route('admin.contacts.index'))
        ->assertSessionHas('status');

    $this->assertDatabaseMissing('contacts', [
        'id' => $contact->id,
    ]);

    $this->assertDatabaseHas('vouchers', [
        'id' => $voucher->id,
        'contact_id' => null,
    ]);
});
