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
        // Main service_types table
        Schema::create('service_types', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->unsignedInteger('duration')->default(60); // Default 60 minutes
            $table->decimal('price', 10, 2); // Price with 2 decimals
            $table->string('image')->nullable(); // Image path
            $table->boolean('status')->default(true); // Active/Inactive
            $table->timestamps();
        });

        // Service type translations table
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

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_type_translations');
        Schema::dropIfExists('service_types');
    }
};
