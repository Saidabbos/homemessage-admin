<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Eski slots va master_slot_bookings jadvallarini o'chirish.
 * 
 * Yangi dizaynda slotlar dinamik hisoblanadi:
 * - Master shift (shift_start, shift_end)
 * - Mavjud buyurtmalar (orders.arrival_window_start/end)
 * - SlotCalculationService orqali
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1. Orders jadvalidan slot_id FK ni olib tashlash
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['slot_id']);
            $table->dropColumn('slot_id');
        });

        // 2. Endi jadvallarni o'chiramiz
        Schema::dropIfExists('master_slot_bookings');
        Schema::dropIfExists('slots');
    }

    public function down(): void
    {
        // slots jadvali
        Schema::create('slots', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->time('start_time');
            $table->time('end_time');
            $table->enum('status', ['free', 'pending', 'reserved'])->default('free');
            $table->timestamps();
            
            $table->index(['date', 'start_time']);
        });

        // master_slot_bookings jadvali
        Schema::create('master_slot_bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('master_id')->constrained()->onDelete('cascade');
            $table->foreignId('slot_id')->constrained()->onDelete('cascade');
            $table->foreignId('order_id')->nullable()->constrained()->onDelete('set null');
            $table->enum('status', ['available', 'busy', 'blocked'])->default('available');
            $table->timestamps();
            
            $table->unique(['master_id', 'slot_id']);
        });

        // orders ga slot_id qayta qo'shish
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('slot_id')->nullable()->after('customer_id')->constrained()->onDelete('set null');
        });
    }
};
