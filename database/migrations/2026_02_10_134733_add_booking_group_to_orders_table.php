<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Multi-master booking support.
     * When multiple masters are booked together, they share the same booking_group_id.
     * Each master gets their own order, but they are linked for:
     * - Same arrival window
     * - Same customer
     * - Same date
     * - Grouped display in admin
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->uuid('booking_group_id')->nullable()->after('order_number');
            $table->index('booking_group_id');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['booking_group_id']);
            $table->dropColumn('booking_group_id');
        });
    }
};
