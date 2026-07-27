<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Group every pre-existing voucher under an "Opening Ceremony" event.
     */
    public function up(): void
    {
        $unassignedVouchers = DB::table('vouchers')->whereNull('event_id');

        if (! $unassignedVouchers->exists()) {
            return;
        }

        $eventId = DB::table('events')->insertGetId([
            'name' => 'Opening Ceremony',
            'slug' => $this->uniqueSlug(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $unassignedVouchers->update(['event_id' => $eventId]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $eventId = DB::table('events')->where('name', 'Opening Ceremony')->value('id');

        if ($eventId === null) {
            return;
        }

        DB::table('vouchers')->where('event_id', $eventId)->update(['event_id' => null]);
        DB::table('events')->where('id', $eventId)->delete();
    }

    private function uniqueSlug(): string
    {
        do {
            $slug = Str::lower(Str::random(8));
        } while (DB::table('events')->where('slug', $slug)->exists());

        return $slug;
    }
};
