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
        Schema::table('sasaran_kegiatan', function (Blueprint $table) {
            $table->enum('assigned_to_type', ['admin', 'kk'])->default('admin');
            $table->foreignUuid('unit_id')->nullable()->constrained('units')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sasaran_kegiatan', function (Blueprint $table) {
            $table->dropForeign(['unit_id']);
            $table->dropColumn(['assigned_to_type', 'unit_id']);
        });
    }
};
