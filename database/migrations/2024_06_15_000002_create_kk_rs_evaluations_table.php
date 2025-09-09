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
        Schema::create('kk_rs_evaluations', function (Blueprint $table): void {
            $table->uuid('id');

            $table->text('evaluation');
            $table->text('follow_up');

            $table->timestamps();
            $table->softDeletes();

            $table->primary('id');

            $table->foreignUuid('kk_rs_achievement_id')->constrained('kk_rs_achievements');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kk_rs_evaluations');
    }
};