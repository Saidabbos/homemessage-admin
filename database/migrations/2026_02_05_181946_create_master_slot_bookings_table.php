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
        Schema::create('master_slot_bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('master_id')->constrained()->onDelete('cascade');
            $table->foreignId('slot_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->enum('status', ['FREE', 'PENDING', 'RESERVED', 'BLOCKED'])->default('FREE');
            $table->unsignedBigInteger('order_id')->nullable(); // FK added when orders table created
            $table->string('block_reason')->nullable();
            $table->timestamps();

            $table->unique(['master_id', 'slot_id', 'date']);
            $table->index(['master_id', 'date']);
            $table->index(['date', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_slot_bookings');
    }
};
