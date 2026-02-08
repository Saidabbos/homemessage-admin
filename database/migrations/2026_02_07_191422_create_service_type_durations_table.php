<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create service_type_durations table
        Schema::create('service_type_durations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_type_id')->constrained()->onDelete('cascade');
            $table->unsignedInteger('duration'); // minutes: 30, 60, 90, 120
            $table->decimal('price', 12, 2); // Price in UZS
            $table->boolean('is_default')->default(false); // Default option for this service
            $table->boolean('status')->default(true); // Active/Inactive
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            // Unique constraint: one price per duration per service
            $table->unique(['service_type_id', 'duration']);
        });

        // Migrate existing data from service_types to service_type_durations
        $serviceTypes = DB::table('service_types')->get();
        foreach ($serviceTypes as $serviceType) {
            DB::table('service_type_durations')->insert([
                'service_type_id' => $serviceType->id,
                'duration' => $serviceType->duration,
                'price' => $serviceType->price,
                'is_default' => true,
                'status' => true,
                'sort_order' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Remove duration and price columns from service_types
        Schema::table('service_types', function (Blueprint $table) {
            $table->dropColumn(['duration', 'price']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Add back duration and price columns
        Schema::table('service_types', function (Blueprint $table) {
            $table->unsignedInteger('duration')->default(60)->after('description');
            $table->decimal('price', 12, 2)->default(0)->after('duration');
        });

        // Restore data from durations (use default or first)
        $durations = DB::table('service_type_durations')
            ->where('is_default', true)
            ->orWhere('sort_order', 0)
            ->get()
            ->unique('service_type_id');

        foreach ($durations as $duration) {
            DB::table('service_types')
                ->where('id', $duration->service_type_id)
                ->update([
                    'duration' => $duration->duration,
                    'price' => $duration->price,
                ]);
        }

        Schema::dropIfExists('service_type_durations');
    }
};
