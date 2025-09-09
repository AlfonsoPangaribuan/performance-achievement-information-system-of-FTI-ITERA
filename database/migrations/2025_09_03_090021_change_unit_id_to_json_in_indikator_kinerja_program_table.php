<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop foreign key constraint first
        Schema::table('indikator_kinerja_program', function (Blueprint $table) {
            $table->dropForeign('indikator_kinerja_program_unit_id_foreign');
        });

        // Clear existing data first to avoid conversion issues
        DB::statement('UPDATE indikator_kinerja_program SET unit_id = NULL WHERE unit_id IS NOT NULL');

        Schema::table('indikator_kinerja_program', function (Blueprint $table) {
            $table->json('unit_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('indikator_kinerja_program', function (Blueprint $table) {
            $table->string('unit_id')->nullable()->change();
            $table->foreign('unit_id')->references('id')->on('units')->onDelete('cascade');
        });
    }
};
