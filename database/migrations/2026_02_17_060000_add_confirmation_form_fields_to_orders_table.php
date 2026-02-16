<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Confirmation call outcome
            if (!Schema::hasColumn('orders', 'call_outcome')) {
                $table->string('call_outcome')->nullable()->after('confirmed_at');
            }

            // Confirmed address details
            if (!Schema::hasColumn('orders', 'conf_entrance')) {
                $table->string('conf_entrance', 50)->nullable()->after('call_outcome');
            }
            if (!Schema::hasColumn('orders', 'conf_floor')) {
                $table->string('conf_floor', 20)->nullable()->after('conf_entrance');
            }
            if (!Schema::hasColumn('orders', 'conf_elevator')) {
                $table->boolean('conf_elevator')->default(false)->after('conf_floor');
            }
            if (!Schema::hasColumn('orders', 'conf_parking')) {
                $table->string('conf_parking', 200)->nullable()->after('conf_elevator');
            }
            if (!Schema::hasColumn('orders', 'conf_landmark')) {
                $table->string('conf_landmark', 200)->nullable()->after('conf_parking');
            }
            if (!Schema::hasColumn('orders', 'conf_onsite_phone')) {
                $table->string('conf_onsite_phone', 20)->nullable()->after('conf_landmark');
            }

            // Restrictions and notes
            if (!Schema::hasColumn('orders', 'conf_constraints')) {
                $table->text('conf_constraints')->nullable()->after('conf_onsite_phone');
            }
            if (!Schema::hasColumn('orders', 'conf_space_ok')) {
                $table->boolean('conf_space_ok')->default(false)->after('conf_constraints');
            }
            if (!Schema::hasColumn('orders', 'conf_pets')) {
                $table->boolean('conf_pets')->default(false)->after('conf_space_ok');
            }
            if (!Schema::hasColumn('orders', 'conf_note_to_master')) {
                $table->text('conf_note_to_master')->nullable()->after('conf_pets');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'call_outcome',
                'conf_entrance',
                'conf_floor',
                'conf_elevator',
                'conf_parking',
                'conf_landmark',
                'conf_onsite_phone',
                'conf_constraints',
                'conf_space_ok',
                'conf_pets',
                'conf_note_to_master',
            ]);
        });
    }
};
