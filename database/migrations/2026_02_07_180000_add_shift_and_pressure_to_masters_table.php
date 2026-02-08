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
        Schema::table('masters', function (Blueprint $table) {
            // Ish vaqti (smena)
            $table->time('shift_start')->default('08:00')->after('experience_years');
            $table->time('shift_end')->default('22:00')->after('shift_start');
            
            // Qo'llab-quvvatlanadigan kuch darajalari (JSON array)
            // ['soft', 'medium', 'hard'] yoki subset
            $table->json('pressure_levels')->nullable()->after('shift_end');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('masters', function (Blueprint $table) {
            $table->dropColumn(['shift_start', 'shift_end', 'pressure_levels']);
        });
    }
};
