<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sa96_registrations', function (Blueprint $table) {
            $table->id();
            $table->text('name')->nullable();
            $table->text('phone')->nullable();
            $table->string('phone_hash', 64)->unique();
            $table->unsignedSmallInteger('birth_year')->nullable();
            $table->string('locale', 5);
            $table->boolean('consent_privacy_notice')->default(false);
            $table->boolean('consent_campaign')->default(false);
            $table->boolean('consent_capacity')->default(false);
            $table->boolean('consent_cross_border')->default(false);
            $table->boolean('consent_marketing')->default(false);
            $table->string('consent_notice_version');
            $table->string('consent_method');
            $table->json('consent_snapshot');
            $table->timestamp('consented_at');
            $table->text('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('withdrawn_at')->nullable()->index();
            $table->timestamps();

            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sa96_registrations');
    }
};
