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
        Schema::table('orders', function (Blueprint $table) {
            // Work Order tracking
            $table->timestamp('work_order_sent_at')->nullable()->after('ready_sent_at');
            
            // QA fields
            $table->boolean('qa_completed')->default(false)->after('dispatcher_notes');
            $table->tinyInteger('qa_overall_rating')->nullable()->after('qa_completed');
            $table->tinyInteger('qa_punctuality_rating')->nullable()->after('qa_overall_rating');
            $table->tinyInteger('qa_professionalism_rating')->nullable()->after('qa_punctuality_rating');
            $table->text('qa_feedback')->nullable()->after('qa_professionalism_rating');
            $table->timestamp('qa_completed_at')->nullable()->after('qa_feedback');
            $table->foreignId('qa_completed_by')->nullable()->constrained('users')->nullOnDelete()->after('qa_completed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['qa_completed_by']);
            $table->dropColumn([
                'work_order_sent_at',
                'qa_completed',
                'qa_overall_rating',
                'qa_punctuality_rating',
                'qa_professionalism_rating',
                'qa_feedback',
                'qa_completed_at',
                'qa_completed_by',
            ]);
        });
    }
};
