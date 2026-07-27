<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $this->withoutContactForeignKey(function (): void {
            Schema::table('vouchers', function (Blueprint $table) {
                $table->dropUnique(['contact_id']);
                $table->index('contact_id');
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $this->withoutContactForeignKey(function (): void {
            Schema::table('vouchers', function (Blueprint $table) {
                $table->dropIndex(['contact_id']);
                $table->unique('contact_id');
            });
        });
    }

    /**
     * MySQL refuses to drop an index backing a foreign key, so temporarily
     * drop the contact foreign key around the index swap. SQLite neither
     * needs nor supports this.
     */
    private function withoutContactForeignKey(Closure $callback): void
    {
        $juggleForeignKey = DB::connection()->getDriverName() !== 'sqlite';

        if ($juggleForeignKey) {
            Schema::table('vouchers', function (Blueprint $table) {
                $table->dropForeign(['contact_id']);
            });
        }

        $callback();

        if ($juggleForeignKey) {
            Schema::table('vouchers', function (Blueprint $table) {
                $table->foreign('contact_id')
                    ->references('id')
                    ->on('contacts')
                    ->nullOnDelete();
            });
        }
    }
};
