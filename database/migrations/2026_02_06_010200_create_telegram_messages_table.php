<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('telegram_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // recipient

            // Message info
            $table->enum('type', [
                'new_order',        // Yangi buyurtma
                'order_confirmed',  // Buyurtma tasdiqlandi
                'payment_received', // To'lov qabul qilindi
                'order_ready',      // Buyurtma tayyor (master yo'lda)
                'order_completed',  // Buyurtma yakunlandi
                'order_cancelled',  // Buyurtma bekor qilindi
                'reminder',         // Eslatma
                'custom',           // Boshqa xabar
            ]);

            $table->string('chat_id')->nullable(); // Telegram chat ID
            $table->integer('message_id')->nullable(); // Telegram message ID
            $table->text('content')->nullable(); // Message content

            // Status
            $table->enum('status', [
                'pending',   // Kutilmoqda
                'sent',      // Yuborildi
                'delivered', // Yetkazildi
                'failed',    // Xatolik
            ])->default('pending');

            $table->text('error_message')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->index(['order_id', 'type']);
            $table->index('status');
            $table->index('chat_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('telegram_messages');
    }
};
