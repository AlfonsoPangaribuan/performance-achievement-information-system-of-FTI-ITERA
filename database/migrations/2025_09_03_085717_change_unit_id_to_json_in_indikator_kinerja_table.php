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
        // Clear existing data first to avoid conversion issues
        DB::statement('UPDATE indikator_kinerja SET unit_id = NULL WHERE unit_id IS NOT NULL');

        Schema::table('indikator_kinerja', function (Blueprint $table) {
            $table->json('unit_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('indikator_kinerja', function (Blueprint $table) {
            // Convert back to string and add foreign key
            $table->string('unit_id')->nullable()->change();
            $table->foreign('unit_id')->references('id')->on('units')->onDelete('cascade');
        });
    }
};
