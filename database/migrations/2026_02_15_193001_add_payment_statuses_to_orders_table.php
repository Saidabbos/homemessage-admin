<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE orders MODIFY COLUMN payment_status ENUM('NOT_PAID','PENDING','PAID','FAILED','REFUNDED','CANCELLED') NOT NULL DEFAULT 'NOT_PAID'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE orders MODIFY COLUMN payment_status ENUM('NOT_PAID','PAID','REFUNDED') NOT NULL DEFAULT 'NOT_PAID'");
    }
};
