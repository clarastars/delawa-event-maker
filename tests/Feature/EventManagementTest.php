<?php

use App\Models\Event;
use App\Models\User;
use App\Models\Voucher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

test('guests are redirected to login for event pages', function () {
    $this->get(route('admin.events.index'))->assertRedirect(route('admin.login'));
});

test('admin can create an event with a generated short link slug', function () {
    $admin = User::factory()->create();

    $response = $this->actingAs($admin)
        ->post(route('admin.events.store'), [
            'name' => 'Ramadan Campaign',
        ]);

    $event = Event::firstWhere('name', 'Ramadan Campaign');

    expect($event)->not->toBeNull()
        ->and($event->slug)->toHaveLength(8);

    $response->assertRedirect(route('admin.events.show', $event));
});

test('admin event page shows the public invite link', function () {
    $admin = User::factory()->create();
    $event = Event::factory()->create();

    $this->actingAs($admin)
        ->get(route('admin.events.show', $event))
        ->assertSuccessful()
        ->assertSee($event->name)
        ->assertSee(route('event.invite', $event));
});

test('admin can upload and replace an event banner', function () {
    Storage::fake('public');

    $admin = User::factory()->create();
    $event = Event::factory()->create();

    $this->actingAs($admin)
        ->post(route('admin.events.banner.update', $event), [
            'banner' => UploadedFile::fake()->image('banner.png', 1200, 630),
        ])
        ->assertRedirect(route('admin.events.show', $event));

    $firstBannerPath = $event->fresh()->banner_path;

    expect($firstBannerPath)->not->toBeNull();
    Storage::disk('public')->assertExists($firstBannerPath);

    $this->actingAs($admin)
        ->post(route('admin.events.banner.update', $event), [
            'banner' => UploadedFile::fake()->image('new-banner.jpg', 1200, 630),
        ])
        ->assertRedirect(route('admin.events.show', $event));

    Storage::disk('public')->assertMissing($firstBannerPath);
    Storage::disk('public')->assertExists($event->fresh()->banner_path);
});

test('event banner must be an image', function () {
    Storage::fake('public');

    $admin = User::factory()->create();
    $event = Event::factory()->create();

    $this->actingAs($admin)
        ->post(route('admin.events.banner.update', $event), [
            'banner' => UploadedFile::fake()->create('banner.pdf', 100, 'application/pdf'),
        ])
        ->assertSessionHasErrors('banner');
});

test('admin can rename an event and update maps link', function () {
    $admin = User::factory()->create();
    $event = Event::factory()->create(['name' => 'Old Name']);

    $this->actingAs($admin)
        ->put(route('admin.events.update', $event), [
            'name' => 'New Name',
            'maps_link' => 'https://maps.google.com/test',
            'maps_link_label' => 'View on Maps',
        ])
        ->assertRedirect(route('admin.events.show', $event));

    expect($event->fresh()->name)->toBe('New Name')
        ->and($event->fresh()->maps_link)->toBe('https://maps.google.com/test')
        ->and($event->fresh()->maps_link_label)->toBe('View on Maps');
});

test('admin cannot delete an event that still has vouchers', function () {
    $admin = User::factory()->create();
    $event = Event::factory()->create();

    Voucher::create([
        'event_id' => $event->id,
        'voucher_id' => 'EG-SA-001',
        'creation_date' => now()->toDateString(),
        'balance' => 100,
        'status' => Voucher::STATUS_ACTIVE,
        'one_time_redemption' => true,
    ]);

    $this->actingAs($admin)
        ->delete(route('admin.events.destroy', $event))
        ->assertRedirect(route('admin.events.show', $event))
        ->assertSessionHasErrors('event');

    $this->assertModelExists($event);
});

test('admin can delete an event without vouchers', function () {
    $admin = User::factory()->create();
    $event = Event::factory()->create();

    $this->actingAs($admin)
        ->delete(route('admin.events.destroy', $event))
        ->assertRedirect(route('admin.events.index'))
        ->assertSessionHas('status');

    $this->assertModelMissing($event);
});

test('admin can upload vouchers into an event', function () {
    $admin = User::factory()->create();
    $event = Event::factory()->create();

    $file = UploadedFile::fake()->createWithContent(
        'vouchers.tsv',
        "entryId\tbalance\tcurrencyCode\tActiveFrom\tExpiryDate\tStatus\tOneTimeRedemption\n".
        "101229318\t400\tSAR\t5/15/26\t6/1/2027\t2\t0\n"
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
    ]);
});

test('voucher upload requires an event', function () {
    $admin = User::factory()->create();

    $file = UploadedFile::fake()->createWithContent(
        'vouchers.tsv',
        "entryId\tbalance\tcurrencyCode\tActiveFrom\tExpiryDate\tStatus\tOneTimeRedemption\n".
        "101229318\t400\tSAR\t5/15/26\t6/1/2027\t2\t0\n"
    );

    $this->actingAs($admin)
        ->post(route('admin.vouchers.upload.store'), [
            'vouchers' => $file,
        ])
        ->assertSessionHasErrors('event_id');
});

test('the opening ceremony migration groups all pre-existing vouchers', function () {
    Voucher::create([
        'voucher_id' => 'LEGACY-001',
        'creation_date' => now()->toDateString(),
        'balance' => 400,
        'status' => Voucher::STATUS_ACTIVE,
        'one_time_redemption' => true,
    ]);

    Voucher::create([
        'voucher_id' => 'LEGACY-002',
        'creation_date' => now()->toDateString(),
        'balance' => 400,
        'status' => Voucher::STATUS_REDEEMED,
        'one_time_redemption' => true,
    ]);

    $migration = require database_path('migrations/2026_07_02_144649_assign_existing_vouchers_to_opening_ceremony_event.php');
    $migration->up();

    $event = Event::firstWhere('name', 'Opening Ceremony');

    expect($event)->not->toBeNull()
        ->and(Voucher::where('event_id', $event->id)->count())->toBe(2)
        ->and(Voucher::whereNull('event_id')->count())->toBe(0);
});
