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
        Schema::create('kk_iku_unit_statuses', function (Blueprint $table): void {
            $table->uuid('id');

            $table->boolean('is_active')->default(false);

            $table->timestamps();
            $table->softDeletes();

            $table->primary('id');

            $table->foreignUuid('kk_id')->constrained('kks');
            $table->foreignUuid('period_id')->constrained('iku_periods');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kk_iku_unit_statuses');
    }
};