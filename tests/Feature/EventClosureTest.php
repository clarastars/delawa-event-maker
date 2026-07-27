<?php

use App\Models\Contact;
use App\Models\Event;
use App\Models\User;
use App\Models\Voucher;
use App\Services\EventClosureMetrics;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

test('admin events index shows close button for open events', function () {
    $admin = User::factory()->create();
    $event = Event::factory()->create(['name' => 'Ramadan Campaign']);

    $this->actingAs($admin)
        ->get(route('admin.events.index'))
        ->assertSuccessful()
        ->assertSee('Close')
        ->assertSee('Open');
});

test('admin can view event closeout page with performance metrics', function () {
    $admin = User::factory()->create();
    $event = Event::factory()->create(['name' => 'Ramadan Campaign']);

    $contact = Contact::create([
        'name' => 'Sara Ahmed',
        'phone' => '0551234567',
        'phone_normalized' => Contact::normalizePhone('0551234567'),
        'activated_at' => now(),
    ]);

    Voucher::create([
        'event_id' => $event->id,
        'contact_id' => $contact->id,
        'voucher_id' => 'EG-SA-100',
        'creation_date' => now()->toDateString(),
        'balance' => 200,
        'remaining_balance' => 150,
        'remaining_balance_synced_at' => now(),
        'status' => Voucher::STATUS_ACTIVE,
        'one_time_redemption' => true,
    ]);

    Voucher::create([
        'event_id' => $event->id,
        'voucher_id' => 'EG-SA-101',
        'creation_date' => now()->toDateString(),
        'balance' => 100,
        'status' => Voucher::STATUS_ACTIVE,
        'one_time_redemption' => true,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.events.close.create', $event))
        ->assertSuccessful()
        ->assertSee('Project closeout')
        ->assertSee('Lessons learned')
        ->assertSee('Recommendations for future events')
        ->assertSee('200.00')
        ->assertSee('Fully spent');
});

test('admin can close an event and generate closure reports', function () {
    Storage::fake('local');

    $admin = User::factory()->create();
    $event = Event::factory()->create(['name' => 'Ramadan Campaign']);

    $contact = Contact::create([
        'name' => 'Sara Ahmed',
        'phone' => '0551234567',
        'phone_normalized' => Contact::normalizePhone('0551234567'),
        'activated_at' => now(),
    ]);

    Voucher::create([
        'event_id' => $event->id,
        'contact_id' => $contact->id,
        'voucher_id' => 'EG-SA-100',
        'creation_date' => now()->toDateString(),
        'balance' => 200,
        'remaining_balance' => 50,
        'remaining_balance_synced_at' => now(),
        'status' => Voucher::STATUS_ACTIVE,
        'one_time_redemption' => true,
    ]);

    $this->actingAs($admin)
        ->post(route('admin.events.close.store', $event), [
            'closure_observations' => 'Guests activated quickly after distribution.',
            'closure_lessons_learned' => 'Send reminders 24 hours earlier.',
            'closure_recommendations' => 'Approve a follow-up event with 25% more budget.',
            'confirmed' => '1',
        ])
        ->assertRedirect(route('admin.events.closure.show', $event))
        ->assertSessionHas('status');

    $event->refresh();

    expect($event->isClosed())->toBeTrue()
        ->and($event->closed_by_user_id)->toBe($admin->id)
        ->and($event->closure_observations)->toBe('Guests activated quickly after distribution.')
        ->and($event->closure_lessons_learned)->toBe('Send reminders 24 hours earlier.')
        ->and($event->closure_recommendations)->toBe('Approve a follow-up event with 25% more budget.')
        ->and($event->closure_pdf_path)->not->toBeNull()
        ->and($event->closure_register_path)->not->toBeNull();

    Storage::disk('local')->assertExists($event->closure_pdf_path);
    Storage::disk('local')->assertExists($event->closure_register_path);
});

test('closure pdf includes submitted notes including arabic text', function () {
    Storage::fake('local');

    $admin = User::factory()->create();
    $event = Event::factory()->create(['name' => 'Ramadan Campaign']);

    $arabicObservations = 'تم توزيع القسائم بنجاح على جميع الضيوف.';
    $arabicLessons = 'يجب إرسال التذكيرات قبل يوم واحد من الفعالية.';
    $arabicRecommendations = 'نوصي باعتماد ميزانية أكبر للفعالية القادمة.';

    $this->actingAs($admin)
        ->post(route('admin.events.close.store', $event), [
            'closure_observations' => $arabicObservations,
            'closure_lessons_learned' => $arabicLessons,
            'closure_recommendations' => $arabicRecommendations,
            'confirmed' => '1',
        ]);

    $event->refresh();

    $html = view('admin.reports.event-closure-pdf', [
        'event' => $event,
        'closedBy' => $admin,
        'metrics' => app(EventClosureMetrics::class)->forEvent($event),
        'closureNotes' => [
            'observations' => $event->closure_observations,
            'lessons_learned' => $event->closure_lessons_learned,
            'recommendations' => $event->closure_recommendations,
        ],
        'generatedAt' => now(),
    ])->render();

    expect($html)
        ->toContain($arabicObservations)
        ->toContain($arabicLessons)
        ->toContain($arabicRecommendations)
        ->not->toContain('No observations recorded.');
});

test('admin can regenerate closure pdf from saved notes', function () {
    Storage::fake('local');

    $admin = User::factory()->create();
    $event = Event::factory()->create(['name' => 'Ramadan Campaign']);

    $this->actingAs($admin)
        ->post(route('admin.events.close.store', $event), [
            'closure_observations' => 'ملاحظات الحدث',
            'confirmed' => '1',
        ]);

    $event->refresh();

    $this->travel(1)->second();

    $this->actingAs($admin)
        ->post(route('admin.events.closure.pdf.regenerate', $event))
        ->assertRedirect(route('admin.events.closure.show', $event))
        ->assertSessionHas('status');

    $event->refresh();

    Storage::disk('local')->assertExists($event->closure_pdf_path);

    expect(Storage::disk('local')->get($event->closure_pdf_path))->toStartWith('%PDF');
});

test('closing an event requires confirmation', function () {
    $admin = User::factory()->create();
    $event = Event::factory()->create();

    $this->actingAs($admin)
        ->from(route('admin.events.close.create', $event))
        ->post(route('admin.events.close.store', $event), [
            'closure_observations' => 'Done.',
        ])
        ->assertRedirect(route('admin.events.close.create', $event))
        ->assertSessionHasErrors('confirmed');

    expect($event->fresh()->isClosed())->toBeFalse();
});

test('admin can download closure pdf and voucher register for closed events', function () {
    Storage::fake('local');

    $admin = User::factory()->create();
    $event = Event::factory()->create(['name' => 'Ramadan Campaign']);

    Voucher::create([
        'event_id' => $event->id,
        'voucher_id' => 'EG-SA-100',
        'creation_date' => now()->toDateString(),
        'balance' => 200,
        'status' => Voucher::STATUS_ACTIVE,
        'one_time_redemption' => true,
    ]);

    $this->actingAs($admin)
        ->post(route('admin.events.close.store', $event), [
            'confirmed' => '1',
        ]);

    $event->refresh();

    $this->actingAs($admin)
        ->get(route('admin.events.closure.pdf', $event))
        ->assertSuccessful()
        ->assertHeader('content-disposition');

    $register = $this->actingAs($admin)
        ->get(route('admin.events.closure.register', $event));

    $register->assertSuccessful()
        ->assertHeader('content-disposition');

    expect($register->streamedContent())
        ->toContain('voucher_id')
        ->toContain('EG-SA-100');
});

test('closed events index shows closure report button instead of close', function () {
    $admin = User::factory()->create();
    $event = Event::factory()->closed()->create(['name' => 'Ramadan Campaign']);

    $this->actingAs($admin)
        ->get(route('admin.events.index'))
        ->assertSuccessful()
        ->assertSee('Closure report')
        ->assertDontSee('>Close<');
});

test('public invite routes show event ended page for closed events', function () {
    $event = Event::factory()->closed()->create();

    $this->get(route('event.invite', $event))
        ->assertSuccessful()
        ->assertViewIs('event-ended');
});
