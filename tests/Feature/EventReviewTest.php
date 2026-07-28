<?php

use App\Models\Contact;
use App\Models\Event;
use App\Models\EventReview;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('user can submit an experience review when vouchers are empty or remaining entries are 0', function () {
    $event = Event::factory()->create();
    $contact = Contact::create([
        'name' => 'John Doe',
        'phone' => '0551234567',
        'phone_normalized' => Contact::normalizePhone('0551234567'),
    ]);

    // Verify session
    session(["event_invite.{$event->id}.verified_contact_id" => $contact->id]);

    $response = $this->post(route('event.vouchers.review', ['event' => $event, 'lang' => 'en']), [
        'experience' => 'It was an amazing event!',
    ]);

    $response->assertRedirect(route('event.vouchers', ['event' => $event, 'lang' => 'en']));
    $response->assertSessionHas('status', 'Thank you for your feedback!');

    $this->assertDatabaseHas('event_reviews', [
        'event_id' => $event->id,
        'contact_id' => $contact->id,
        'experience' => 'It was an amazing event!',
    ]);
});

test('admin can view reviews', function () {
    $user = User::factory()->create();
    $event = Event::factory()->create();
    $contact = Contact::create([
        'name' => 'John Doe',
        'phone' => '0551234567',
        'phone_normalized' => Contact::normalizePhone('0551234567'),
    ]);

    EventReview::create([
        'event_id' => $event->id,
        'contact_id' => $contact->id,
        'experience' => 'Great experience',
    ]);

    $response = $this->actingAs($user)->get(route('admin.reviews.index'));

    $response->assertOk();
    $response->assertSee('Great experience');
});
