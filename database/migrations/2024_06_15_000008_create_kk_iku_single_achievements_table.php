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
        Schema::create('kk_iku_single_achievements', function (Blueprint $table): void {
            $table->uuid('id');

            $table->string('realization');

            $table->timestamps();
            $table->softDeletes();

            $table->primary('id');

            $table->foreignUuid('indikator_kinerja_program_id')->constrained('indikator_kinerja_program');
            $table->foreignUuid('period_id')->constrained('iku_periods');
            $table->foreignUuid('kk_id')->constrained('kks');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kk_iku_single_achievements');
    }
};