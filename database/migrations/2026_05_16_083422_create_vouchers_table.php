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
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contact_id')->nullable()->unique()->constrained()->nullOnDelete();
            $table->string('voucher_id')->unique();
            $table->date('creation_date');
            $table->date('expiry_date')->nullable();
            $table->decimal('balance', 10, 2)->default(0);
            $table->string('status')->default('active')->index();
            $table->boolean('one_time_redemption')->default(true);
            $table->timestamp('redeemed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vouchers');
    }
};
