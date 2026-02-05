<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique(); // HM-20260205-001

            // Relations
            $table->foreignId('customer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('master_id')->constrained()->cascadeOnDelete();
            $table->foreignId('slot_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_type_id')->constrained()->cascadeOnDelete();
            $table->foreignId('oil_id')->nullable()->constrained('oils')->nullOnDelete();

            // Booking date (separate from slot template)
            $table->date('booking_date');

            // Status
            $table->enum('status', [
                'NEW',           // Yangi buyurtma
                'CONFIRMING',    // Dispatcher qo'ng'iroq qilmoqda
                'CONFIRMED',     // Tasdiqlangan
                'IN_PROGRESS',   // Xizmat ko'rsatilmoqda
                'COMPLETED',     // Yakunlangan
                'CANCELLED',     // Bekor qilingan
            ])->default('NEW');

            // Payment
            $table->enum('payment_status', [
                'NOT_PAID',
                'PAID',
                'REFUNDED',
            ])->default('NOT_PAID');
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->timestamp('paid_at')->nullable();

            // Customer address info
            $table->text('address')->nullable();
            $table->string('entrance')->nullable();
            $table->string('floor')->nullable();
            $table->string('apartment')->nullable();
            $table->text('landmark')->nullable();
            $table->string('contact_phone')->nullable();

            // Dispatcher notes
            $table->text('dispatcher_notes')->nullable();
            $table->foreignId('confirmed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('confirmed_at')->nullable();

            // Cancellation
            $table->text('cancel_reason')->nullable();
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('cancelled_at')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['status', 'booking_date']);
            $table->index('order_number');
        });

        // Order status history / audit log
        Schema::create('order_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action'); // status_changed, slot_changed, note_added
            $table->string('old_value')->nullable();
            $table->string('new_value')->nullable();
            $table->text('comment')->nullable();
            $table->timestamps();

            $table->index(['order_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_logs');
        Schema::dropIfExists('orders');
    }
};
