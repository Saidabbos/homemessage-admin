<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ratings', function (Blueprint $table) {
            $table->id();
            
            // Rating type: client_to_master or master_to_client
            $table->string('type', 20)->default('client_to_master');
            
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('master_id')->constrained()->onDelete('cascade');
            $table->foreignId('customer_id')->constrained('users')->onDelete('cascade');
            
            // Ratings (1-5 stars)
            $table->unsignedTinyInteger('overall_rating')->nullable(); // Umumiy baho
            $table->unsignedTinyInteger('punctuality_rating')->nullable(); // Vaqtida kelish
            $table->unsignedTinyInteger('professionalism_rating')->nullable(); // Professionallik
            $table->unsignedTinyInteger('cleanliness_rating')->nullable(); // Tozalik
            
            $table->text('feedback')->nullable(); // Izoh
            $table->boolean('is_public')->default(true); // Ommaviy ko'rinadimi
            
            $table->string('token', 64)->unique(); // Rating link uchun
            $table->timestamp('rated_at')->nullable(); // Qachon baholangan
            $table->timestamps();
            
            // Har bir buyurtma turi uchun 1 marta baholanadi
            $table->unique(['order_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ratings');
    }
};
