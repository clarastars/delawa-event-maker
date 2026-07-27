<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('vouchers', function (Blueprint $table) {
            $table->decimal('remaining_balance', 10, 2)->nullable()->after('balance');
            $table->timestamp('remaining_balance_synced_at')->nullable()->after('remaining_balance');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vouchers', function (Blueprint $table) {
            $table->dropColumn(['remaining_balance', 'remaining_balance_synced_at']);
        });
    }
};
