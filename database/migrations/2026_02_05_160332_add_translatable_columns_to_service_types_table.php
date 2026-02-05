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
        // Drop old translations table if exists
        Schema::dropIfExists('service_type_translations');

        // Add JSON columns for Spatie Translatable
        Schema::table('service_types', function (Blueprint $table) {
            $table->json('name')->after('slug');
            $table->json('description')->nullable()->after('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_types', function (Blueprint $table) {
            $table->dropColumn(['name', 'description']);
        });

        // Recreate translations table
        Schema::create('service_type_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_type_id')->constrained('service_types')->onDelete('cascade');
            $table->string('locale')->index();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
            $table->unique(['service_type_id', 'locale']);
        });
    }
};
