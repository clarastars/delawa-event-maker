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
        Schema::table('events', function (Blueprint $table) {
            $table->timestamp('closed_at')->nullable()->after('banner_path');
            $table->foreignId('closed_by_user_id')->nullable()->after('closed_at')->constrained('users')->nullOnDelete();
            $table->text('closure_observations')->nullable()->after('closed_by_user_id');
            $table->text('closure_lessons_learned')->nullable()->after('closure_observations');
            $table->text('closure_recommendations')->nullable()->after('closure_lessons_learned');
            $table->string('closure_pdf_path')->nullable()->after('closure_recommendations');
            $table->string('closure_register_path')->nullable()->after('closure_pdf_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropConstrainedForeignId('closed_by_user_id');
            $table->dropColumn([
                'closed_at',
                'closure_observations',
                'closure_lessons_learned',
                'closure_recommendations',
                'closure_pdf_path',
                'closure_register_path',
            ]);
        });
    }
};
