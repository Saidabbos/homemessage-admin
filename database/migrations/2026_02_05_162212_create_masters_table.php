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
        Schema::create('masters', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('phone')->unique();
            $table->string('email')->nullable()->unique();
            $table->string('photo')->nullable();
            $table->json('bio')->nullable(); // Translatable
            $table->date('birth_date')->nullable();
            $table->enum('gender', ['male', 'female'])->default('female');
            $table->integer('experience_years')->default(0);
            $table->decimal('rating', 2, 1)->default(0); // 0.0 - 5.0
            $table->integer('total_orders')->default(0);
            $table->boolean('status')->default(true);
            $table->timestamps();
        });

        // Pivot table for master-service relationships
        Schema::create('master_service_type', function (Blueprint $table) {
            $table->id();
            $table->foreignId('master_id')->constrained()->onDelete('cascade');
            $table->foreignId('service_type_id')->constrained()->onDelete('cascade');
            $table->unique(['master_id', 'service_type_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_service_type');
        Schema::dropIfExists('masters');
    }
};
