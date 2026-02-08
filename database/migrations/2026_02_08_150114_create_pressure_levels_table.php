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
        Schema::create('pressure_levels', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->json('name'); // Translatable
            $table->json('description')->nullable(); // Translatable
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('status')->default(true);
            $table->timestamps();
        });

        // Pivot table for master-pressure_level relationship
        Schema::create('master_pressure_level', function (Blueprint $table) {
            $table->id();
            $table->foreignId('master_id')->constrained('masters')->onDelete('cascade');
            $table->foreignId('pressure_level_id')->constrained('pressure_levels')->onDelete('cascade');
            $table->unique(['master_id', 'pressure_level_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_pressure_level');
        Schema::dropIfExists('pressure_levels');
    }
};
