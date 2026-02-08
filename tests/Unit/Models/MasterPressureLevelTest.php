<?php

namespace Tests\Unit\Models;

use App\Models\Master;
use App\Models\PressureLevel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MasterPressureLevelTest extends TestCase
{
    use RefreshDatabase;
    /**
     * Test Master has many-to-many relationship with PressureLevels
     */
    public function test_master_has_pressure_levels(): void
    {
        $master = Master::create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'phone' => '+998901234567',
            'gender' => 'male',
            'status' => true,
        ]);

        $light = PressureLevel::create([
            'slug' => 'light',
            'name' => ['uz' => 'Yumshoq'],
            'status' => true,
        ]);

        $medium = PressureLevel::create([
            'slug' => 'medium',
            'name' => ['uz' => 'O\'rtacha'],
            'status' => true,
        ]);

        $master->pressureLevels()->attach([$light->id, $medium->id]);

        $this->assertEquals(2, $master->pressureLevels()->count());
        $this->assertTrue($master->pressureLevels()->where('slug', 'light')->exists());
        $this->assertTrue($master->pressureLevels()->where('slug', 'medium')->exists());
    }

    /**
     * Test Master can detach pressure levels
     */
    public function test_master_can_detach_pressure_levels(): void
    {
        $master = Master::create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'phone' => '+998901234567',
            'gender' => 'male',
            'status' => true,
        ]);

        $light = PressureLevel::create([
            'slug' => 'light',
            'name' => ['uz' => 'Yumshoq'],
            'status' => true,
        ]);

        $master->pressureLevels()->attach($light->id);
        $this->assertEquals(1, $master->pressureLevels()->count());

        $master->pressureLevels()->detach($light->id);
        $this->assertEquals(0, $master->pressureLevels()->count());
    }

    /**
     * Test Master can sync pressure levels
     */
    public function test_master_can_sync_pressure_levels(): void
    {
        $master = Master::create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'phone' => '+998901234567',
            'gender' => 'male',
            'status' => true,
        ]);

        $light = PressureLevel::create([
            'slug' => 'light',
            'name' => ['uz' => 'Yumshoq'],
            'status' => true,
        ]);

        $medium = PressureLevel::create([
            'slug' => 'medium',
            'name' => ['uz' => 'O\'rtacha'],
            'status' => true,
        ]);

        $heavy = PressureLevel::create([
            'slug' => 'heavy',
            'name' => ['uz' => 'Kuchli'],
            'status' => true,
        ]);

        // Initially attach light and medium
        $master->pressureLevels()->attach([$light->id, $medium->id]);
        $this->assertEquals(2, $master->pressureLevels()->count());

        // Sync to only heavy
        $master->pressureLevels()->sync([$heavy->id]);
        $this->assertEquals(1, $master->pressureLevels()->count());
        $this->assertTrue($master->pressureLevels()->where('slug', 'heavy')->exists());
        $this->assertFalse($master->pressureLevels()->where('slug', 'light')->exists());
    }

    /**
     * Test Master supports pressure level helper method
     */
    public function test_master_supports_pressure_level(): void
    {
        $master = Master::create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'phone' => '+998901234567',
            'gender' => 'male',
            'pressure_levels' => ['light', 'medium'],
            'status' => true,
        ]);

        $this->assertTrue($master->supportsPressureLevel('light'));
        $this->assertTrue($master->supportsPressureLevel('medium'));
        $this->assertFalse($master->supportsPressureLevel('heavy'));
    }

    /**
     * Test Master supports all pressure levels when none specified
     */
    public function test_master_supports_all_pressure_levels_when_empty(): void
    {
        $master = Master::create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'phone' => '+998901234567',
            'gender' => 'male',
            'pressure_levels' => null,
            'status' => true,
        ]);

        $this->assertTrue($master->supportsPressureLevel('light'));
        $this->assertTrue($master->supportsPressureLevel('medium'));
        $this->assertTrue($master->supportsPressureLevel('heavy'));
    }
}
