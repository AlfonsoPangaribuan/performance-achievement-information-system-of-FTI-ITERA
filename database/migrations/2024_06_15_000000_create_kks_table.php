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
        Schema::create('kks', function (Blueprint $table): void {
            $table->uuid('id');

            $table->string('short_name', 10);
            $table->string('name');
            
            // Foreign key to units (program studi)
            $table->foreignUuid('unit_id')->constrained('units');

            $table->timestamps();
            $table->softDeletes();

            $table->primary('id');
            $table->unique(['name', 'unit_id']);
        });

        // Update users table to add kk_id
        Schema::table('users', function (Blueprint $table): void {
            $table->foreignUuid('kk_id')->nullable()->constrained('kks');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropForeign(['kk_id']);
            $table->dropColumn('kk_id');
        });

        Schema::dropIfExists('kks');
    }
};