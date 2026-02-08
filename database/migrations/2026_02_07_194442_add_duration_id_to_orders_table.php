<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Orders jadvaliga duration_id ustunini qo'shish.
 * Bu ustun tanlangan davomiylik variantini ko'rsatadi.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('duration_id')
                ->nullable()
                ->after('service_type_id')
                ->constrained('service_type_durations')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['duration_id']);
            $table->dropColumn('duration_id');
        });
    }
};
