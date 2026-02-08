<?php

namespace Tests\Unit\Services;

use App\Models\Master;
use App\Models\PressureLevel;
use App\Models\User;
use App\Services\MasterService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MasterServiceTest extends TestCase
{
    use RefreshDatabase;

    private MasterService $service;

    protected function setUp(): void
    {
        parent::setUp();

        // Create required role
        Role::create(['name' => 'master', 'guard_name' => 'web']);

        $this->service = app(MasterService::class);
    }

    /**
     * Test MasterService creates master with pressure levels
     */
    public function test_master_service_creates_master_with_pressure_levels(): void
    {
        // Create pressure levels
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

        // Create request mock
        $request = new Request([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'phone' => '+998901234567',
            'email' => 'john@example.com',
            'password' => 'password123',
            'gender' => 'male',
            'experience_years' => 5,
            'service_types' => [],
            'oils' => [],
            'pressure_levels' => [$light->id, $medium->id],
            'uz' => ['bio' => 'Test bio'],
            'ru' => ['bio' => 'Test bio'],
            'en' => ['bio' => 'Test bio'],
        ]);

        $master = $this->service->create($request->all(), $request);

        $this->assertNotNull($master);
        $this->assertEquals('John', $master->first_name);
        $this->assertEquals(2, $master->pressureLevels()->count());
        $this->assertTrue($master->pressureLevels()->where('slug', 'light')->exists());
        $this->assertTrue($master->pressureLevels()->where('slug', 'medium')->exists());
    }

    /**
     * Test MasterService updates master pressure levels
     */
    public function test_master_service_updates_master_pressure_levels(): void
    {
        // Create master
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password'),
        ]);

        $master = Master::create([
            'user_id' => $user->id,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'phone' => '+998901234567',
            'email' => 'john@example.com',
            'gender' => 'male',
            'status' => true,
        ]);

        // Create pressure levels
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

        // Attach initial pressure levels
        $master->pressureLevels()->attach([$light->id, $medium->id]);
        $this->assertEquals(2, $master->pressureLevels()->count());

        // Update with new pressure levels
        $request = new Request([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'phone' => '+998901234567',
            'email' => 'john@example.com',
            'password' => '',
            'gender' => 'male',
            'experience_years' => 5,
            'service_types' => [],
            'oils' => [],
            'pressure_levels' => [$medium->id, $heavy->id],
            'uz' => ['bio' => 'Updated bio'],
            'ru' => ['bio' => 'Updated bio'],
            'en' => ['bio' => 'Updated bio'],
        ]);

        $this->service->update($master, $request->all(), $request);

        $master->refresh();
        $this->assertEquals(2, $master->pressureLevels()->count());
        $this->assertFalse($master->pressureLevels()->where('slug', 'light')->exists());
        $this->assertTrue($master->pressureLevels()->where('slug', 'medium')->exists());
        $this->assertTrue($master->pressureLevels()->where('slug', 'heavy')->exists());
    }

    /**
     * Test MasterService getEditData includes pressure levels
     */
    public function test_master_service_get_edit_data_includes_pressure_levels(): void
    {
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password'),
        ]);

        $master = Master::create([
            'user_id' => $user->id,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'phone' => '+998901234567',
            'email' => 'john@example.com',
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

        $editData = $this->service->getEditData($master);

        $this->assertArrayHasKey('pressure_levels', $editData);
        $this->assertEquals(2, count($editData['pressure_levels']));
        $this->assertContains($light->id, $editData['pressure_levels']);
        $this->assertContains($medium->id, $editData['pressure_levels']);
    }

    /**
     * Test MasterService handles empty pressure levels
     */
    public function test_master_service_handles_empty_pressure_levels(): void
    {
        $request = new Request([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'phone' => '+998901234567',
            'email' => 'john@example.com',
            'password' => 'password123',
            'gender' => 'male',
            'experience_years' => 5,
            'service_types' => [],
            'oils' => [],
            'pressure_levels' => [],
            'uz' => ['bio' => 'Test bio'],
            'ru' => ['bio' => 'Test bio'],
            'en' => ['bio' => 'Test bio'],
        ]);

        $master = $this->service->create($request->all(), $request);

        $this->assertNotNull($master);
        $this->assertEquals(0, $master->pressureLevels()->count());
    }
}
