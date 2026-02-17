<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // e.g., "Uy", "Ish", "Boshqa"
            $table->string('address'); // Full address
            $table->string('entrance')->nullable(); // Kirish
            $table->string('floor')->nullable(); // Qavat
            $table->string('apartment')->nullable(); // Xonadon
            $table->string('landmark')->nullable(); // Mo'ljal
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            // User can have multiple addresses, but name should be unique per user
            $table->unique(['user_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_addresses');
    }
};
