<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quality_controls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('checked_by')->nullable()->constrained('users')->nullOnDelete();

            // Ratings (1-5)
            $table->unsignedTinyInteger('rating_overall')->nullable();
            $table->unsignedTinyInteger('rating_punctuality')->nullable();
            $table->unsignedTinyInteger('rating_quality')->nullable();
            $table->unsignedTinyInteger('rating_cleanliness')->nullable();
            $table->unsignedTinyInteger('rating_communication')->nullable();

            // Checklist
            $table->boolean('has_all_items')->default(false); // Master barcha narsalarni olib keldimi
            $table->boolean('arrived_on_time')->default(false); // Vaqtida keldimi
            $table->boolean('proper_uniform')->default(false); // Forma kiyganmi
            $table->boolean('polite_behavior')->default(false); // Xushmuomalalik

            // Customer feedback
            $table->text('customer_feedback')->nullable();
            $table->text('internal_notes')->nullable();

            // Status
            $table->enum('status', ['pending', 'completed', 'issue_reported'])->default('pending');
            $table->timestamp('completed_at')->nullable();

            $table->timestamps();

            $table->index('order_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quality_controls');
    }
};
