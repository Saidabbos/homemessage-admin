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
            $table->string('telegram_id')->nullable()->after('phone');
            $table->string('telegram_username')->nullable()->after('telegram_id');
            $table->boolean('notify_telegram')->default(true)->after('telegram_username');
            $table->boolean('notify_sms')->default(true)->after('notify_telegram');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('masters', function (Blueprint $table) {
            $table->dropColumn(['telegram_id', 'telegram_username', 'notify_telegram', 'notify_sms']);
        });
    }
};
