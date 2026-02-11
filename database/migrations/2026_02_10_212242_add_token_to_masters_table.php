<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('masters', function (Blueprint $table) {
            $table->string('token', 64)->unique()->nullable()->after('status');
        });

        // Generate tokens for existing masters
        \App\Models\Master::whereNull('token')->each(function ($master) {
            $master->update(['token' => Str::random(32)]);
        });
    }

    public function down(): void
    {
        Schema::table('masters', function (Blueprint $table) {
            $table->dropColumn('token');
        });
    }
};
