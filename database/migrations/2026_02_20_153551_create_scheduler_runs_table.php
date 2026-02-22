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
        Schema::create('scheduler_runs', function (Blueprint $table) {
            $table->id();
            $table->string('command');
            $table->enum('status', ['success', 'failed', 'running'])->default('running');
            $table->integer('records_processed')->default(0);
            $table->json('details')->nullable();
            $table->text('error')->nullable();
            $table->timestamp('started_at');
            $table->timestamp('finished_at')->nullable();
            $table->integer('duration_ms')->nullable();
            $table->timestamps();
            
            $table->index(['command', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scheduler_runs');
    }
};
