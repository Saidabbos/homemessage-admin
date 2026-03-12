<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE payments MODIFY COLUMN provider ENUM('payme', 'click', 'uzum') NOT NULL DEFAULT 'payme'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE payments MODIFY COLUMN provider ENUM('payme', 'click') NOT NULL DEFAULT 'payme'");
    }
};
