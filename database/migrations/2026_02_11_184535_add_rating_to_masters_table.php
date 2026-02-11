<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('masters', function (Blueprint $table) {
            $table->decimal('rating', 2, 1)->nullable()->after('status'); // 4.5
            $table->unsignedInteger('rating_count')->default(0)->after('rating');
        });
    }

    public function down(): void
    {
        Schema::table('masters', function (Blueprint $table) {
            $table->dropColumn(['rating', 'rating_count']);
        });
    }
};
