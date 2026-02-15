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
        Schema::create('master_blocked_times', function (Blueprint $table) {
            $table->id();
            $table->foreignId('master_id')->constrained()->onDelete('cascade');
            $table->date('blocked_date');
            $table->time('start_time')->nullable(); // null = kun bo'yi
            $table->time('end_time')->nullable();   // null = kun bo'yi
            $table->string('reason')->nullable();   // ta'til, shaxsiy ish, etc.
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            
            // Indexes
            $table->index(['master_id', 'blocked_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_blocked_times');
    }
};
