<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('restriction_reason')->nullable()->after('status');
            $table->timestamp('restricted_at')->nullable()->after('restriction_reason');
            $table->foreignId('restricted_by')->nullable()->after('restricted_at')
                ->constrained('users')->nullOnDelete();
            $table->text('admin_notes')->nullable()->after('restricted_by');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['restricted_by']);
            $table->dropColumn(['restriction_reason', 'restricted_at', 'restricted_by', 'admin_notes']);
        });
    }
};
