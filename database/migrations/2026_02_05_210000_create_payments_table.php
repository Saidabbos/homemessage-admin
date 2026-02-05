<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();

            // Provider info
            $table->enum('provider', ['payme', 'click'])->default('payme');
            $table->string('transaction_id')->nullable()->unique();
            $table->string('external_id')->nullable(); // Provider's transaction ID

            // Amount
            $table->decimal('amount', 12, 2);
            $table->string('currency', 3)->default('UZS');

            // Status
            $table->enum('status', [
                'PENDING',      // Kutilmoqda
                'PROCESSING',   // Jarayonda
                'PAID',         // To'langan
                'CANCELLED',    // Bekor qilingan
                'FAILED',       // Xatolik
                'REFUNDED',     // Qaytarilgan
            ])->default('PENDING');

            // Payment URL (for redirect)
            $table->text('payment_url')->nullable();

            // Timestamps
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('refunded_at')->nullable();
            $table->timestamp('expires_at')->nullable();

            // Error info
            $table->string('error_code')->nullable();
            $table->text('error_message')->nullable();

            // Raw response from provider (for debugging)
            $table->json('provider_response')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['order_id', 'status']);
            $table->index('provider');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
