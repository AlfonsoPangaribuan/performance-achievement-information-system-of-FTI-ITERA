<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('kk_iku_targets', function (Blueprint $table): void {
            $table->uuid('id');

            $table->string('target');

            $table->timestamps();
            $table->softDeletes();

            $table->primary('id');

            $table->foreignUuid('indikator_kinerja_program_id')->constrained('indikator_kinerja_program');
            $table->foreignUuid('kk_id')->constrained('kks');
            $table->foreignUuid('year_id')->constrained('iku_years');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kk_iku_targets');
    }
};