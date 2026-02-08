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
        Schema::table('orders', function (Blueprint $table) {
            // Arrival window (kelish oynasi)
            $table->time('arrival_window_start')->nullable()->after('booking_date');
            $table->time('arrival_window_end')->nullable()->after('arrival_window_start');
            
            // Kishilar soni (1-4)
            $table->unsignedTinyInteger('people_count')->default(1)->after('oil_id');
            
            // Kuch darajasi
            $table->enum('pressure_level', ['soft', 'medium', 'hard', 'any'])->default('medium')->after('people_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['arrival_window_start', 'arrival_window_end', 'people_count', 'pressure_level']);
        });
    }
};
