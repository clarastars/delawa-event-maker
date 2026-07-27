<?php

use App\Models\Contact;
use App\Models\Event;
use App\Models\User;
use App\Models\Voucher;
use App\Services\OngoingEventsReport;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('ongoing events report summarizes current coupon metrics for an open event', function () {
    $event = Event::factory()->create();
    $contact = Contact::create([
        'name' => 'Sara Ahmed',
        'phone' => '0551234567',
        'phone_normalized' => Contact::normalizePhone('0551234567'),
        'activated_at' => now(),
    ]);

    Voucher::create([
        'event_id' => $event->id,
        'contact_id' => $contact->id,
        'voucher_id' => 'OPEN-ASSIGNED',
        'creation_date' => now()->toDateString(),
        'balance' => 200,
        'remaining_balance' => 150,
        'remaining_balance_synced_at' => now(),
        'status' => Voucher::STATUS_ACTIVE,
        'one_time_redemption' => true,
    ]);

    Voucher::create([
        'event_id' => $event->id,
        'voucher_id' => 'OPEN-AVAILABLE',
        'creation_date' => now()->toDateString(),
        'balance' => 100,
        'status' => Voucher::STATUS_ACTIVE,
        'one_time_redemption' => true,
    ]);

    $report = app(OngoingEventsReport::class)->forEvent($event);

    expect($report['total_coupons'])->toBe(2)
        ->and($report['total_value'])->toBe(300.0)
        ->and($report['assigned_value'])->toBe(200.0)
        ->and($report['used_value'])->toBe(50.0)
        ->and($report['leftover_coupons'])->toBe(1)
        ->and($report['leftover_value'])->toBe(100.0);
});

test('admin events index shows current report button for open events', function () {
    $admin = User::factory()->create();
    $event = Event::factory()->create(['name' => 'Ramadan Campaign']);

    $this->actingAs($admin)
        ->get(route('admin.events.index'))
        ->assertSuccessful()
        ->assertSee('Current Report')
        ->assertDontSee('Ongoing events report')
        ->assertSee(route('admin.events.current-report', $event), false);
});

test('admin can view printable current report for an open event', function () {
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
        'voucher_id' => 'OPEN-ASSIGNED',
        'creation_date' => now()->toDateString(),
        'balance' => 200,
        'remaining_balance' => 150,
        'remaining_balance_synced_at' => now(),
        'status' => Voucher::STATUS_ACTIVE,
        'one_time_redemption' => true,
    ]);

    Voucher::create([
        'event_id' => $event->id,
        'voucher_id' => 'OPEN-AVAILABLE',
        'creation_date' => now()->toDateString(),
        'balance' => 100,
        'status' => Voucher::STATUS_ACTIVE,
        'one_time_redemption' => true,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.events.current-report', $event))
        ->assertSuccessful()
        ->assertSee('التقرير الحالي — Ramadan Campaign', false)
        ->assertSee('dir="rtl"', false)
        ->assertSee('إجمالي القسائم')
        ->assertSee('إجمالي القيمة')
        ->assertSee('إجمالي القيمة المخصصة')
        ->assertSee('إجمالي القيمة المستخدمة')
        ->assertSee('القسائم المتبقية (غير مخصصة)')
        ->assertSee('القيمة المتبقية المتاحة')
        ->assertSee('Download statement')
        ->assertSee(route('admin.events.current-report.statement', $event), false)
        ->assertSee('300.00 SAR')
        ->assertSee('200.00 SAR')
        ->assertSee('50.00 SAR')
        ->assertSee('100.00 SAR')
        ->assertSee('Print');
});

test('admin can download the current report statement csv', function () {
    $admin = User::factory()->create();
    $event = Event::factory()->create(['name' => 'Ramadan Campaign']);
    $contact = Contact::create([
        'name' => 'Sara Ahmed',
        'phone' => '0551234567',
        'phone_normalized' => Contact::normalizePhone('0551234567'),
    ]);

    Voucher::create([
        'event_id' => $event->id,
        'contact_id' => $contact->id,
        'voucher_id' => 'OPEN-ASSIGNED',
        'creation_date' => now()->toDateString(),
        'balance' => 200,
        'status' => Voucher::STATUS_ACTIVE,
        'one_time_redemption' => true,
    ]);

    Voucher::create([
        'event_id' => $event->id,
        'voucher_id' => 'OPEN-AVAILABLE',
        'creation_date' => now()->toDateString(),
        'balance' => 100,
        'status' => Voucher::STATUS_ACTIVE,
        'one_time_redemption' => true,
    ]);

    $response = $this->actingAs($admin)
        ->get(route('admin.events.current-report.statement', $event));

    $response->assertSuccessful()
        ->assertHeader('content-type', 'text/csv; charset=UTF-8');

    $csv = $response->streamedContent();

    expect($csv)
        ->toContain('رقم القسيمة')
        ->toContain('الاسم')
        ->toContain('الجوال')
        ->toContain('القيمة')
        ->toContain('OPEN-ASSIGNED')
        ->toContain('Sara Ahmed')
        ->toContain('0551234567')
        ->toContain('200.00')
        ->toContain('OPEN-AVAILABLE')
        ->toContain('100.00');
});

test('current report redirects closed events to the closure report', function () {
    $admin = User::factory()->create();
    $event = Event::factory()->closed()->create();

    $this->actingAs($admin)
        ->get(route('admin.events.current-report', $event))
        ->assertRedirect(route('admin.events.closure.show', $event));
});

test('statement download redirects closed events to the closure report', function () {
    $admin = User::factory()->create();
    $event = Event::factory()->closed()->create();

    $this->actingAs($admin)
        ->get(route('admin.events.current-report.statement', $event))
        ->assertRedirect(route('admin.events.closure.show', $event));
});
