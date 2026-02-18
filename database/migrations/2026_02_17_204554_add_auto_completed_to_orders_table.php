<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->boolean('auto_completed')->default(false)->after('status');
            $table->timestamp('completed_at')->nullable()->after('auto_completed');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['auto_completed', 'completed_at']);
        });
    }
};
