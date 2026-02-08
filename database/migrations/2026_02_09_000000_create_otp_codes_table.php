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
        Schema::create('otp_codes', function (Blueprint $table) {
            $table->id();
            $table->string('phone', 20)->index(); // +998XXXXXXXXX format
            $table->string('code_hash'); // Hashed OTP code (bcrypt)
            $table->timestamp('expires_at')->index(); // OTP expiry (3 minutes)
            $table->timestamp('verified_at')->nullable(); // Verification timestamp
            $table->unsignedTinyInteger('verify_attempts')->default(0); // Failed verification attempts
            $table->string('ip_address', 45)->nullable(); // Request IP
            $table->text('user_agent')->nullable(); // User agent
            $table->timestamps();

            // Indexes for rate limit queries
            $table->index(['phone', 'created_at']);
            $table->index(['ip_address', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('otp_codes');
    }
};
