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
        // Drop foreign key if it exists
        try {
            Schema::table('indikator_kinerja', function (Blueprint $table) {
                $table->dropForeign(['unit_id']);
            });
        } catch (\Exception $e) {
            // Foreign key doesn't exist, continue
        }

        // Change the column from uuid to json
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
            // Change back to uuid
            $table->uuid('unit_id')->nullable()->change();

            // Re-add the foreign key constraint
            $table->foreign('unit_id')->references('id')->on('units')->onDelete('cascade');
        });
    }
};
