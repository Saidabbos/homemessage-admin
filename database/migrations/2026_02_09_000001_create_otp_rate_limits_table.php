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
        Schema::create('otp_rate_limits', function (Blueprint $table) {
            $table->id();
            $table->string('phone', 20)->index();
            $table->string('ip_address', 45)->index();
            $table->enum('action', ['send', 'verify']); // send=SMS request, verify=code attempt
            $table->timestamp('blocked_until')->nullable(); // Block expiry
            $table->unsignedTinyInteger('attempts_count')->default(1);
            $table->timestamps();

            // Unique constraints
            $table->unique(['phone', 'action']);
            $table->index(['ip_address', 'action']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('otp_rate_limits');
    }
};
