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
        Schema::create('kk_iku_achievement_data', function (Blueprint $table): void {
            $table->uuid('id');

            $table->string('value');
            $table->integer('column_index');

            $table->timestamps();
            $table->softDeletes();

            $table->primary('id');

            $table->foreignUuid('kk_iku_achievement_id')->constrained('kk_iku_achievements');
            $table->foreignUuid('column_id')->constrained('ikp_columns');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kk_iku_achievement_data');
    }
};