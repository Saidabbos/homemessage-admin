<?php

namespace Tests\Feature;

use App\Models\Master;
use App\Models\PressureLevel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MasterPressureLevelIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // Create admin role and user
        Role::create(['name' => 'admin', 'guard_name' => 'web']);

        $this->admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->admin->assignRole('admin');
    }

    /**
     * Test master can be created with pressure levels
     */
    public function test_master_can_be_created_with_pressure_levels(): void
    {
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

        $response = $this->actingAs($this->admin)
            ->post(route('admin.masters.store'), [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'phone' => '+998901234567',
                'email' => 'john@example.com',
                'password' => 'password123',
                'gender' => 'male',
                'birth_date' => '1990-01-01',
                'experience_years' => 5,
                'status' => true,
                'service_types' => [],
                'oils' => [],
                'pressure_levels' => [$light->id, $medium->id],
                'uz' => ['bio' => 'Test bio'],
                'ru' => ['bio' => 'Test bio'],
                'en' => ['bio' => 'Test bio'],
            ]);

        $response->assertRedirect(route('admin.masters.index'));

        $master = Master::where('email', 'john@example.com')->first();
        $this->assertNotNull($master);
        $this->assertEquals(2, $master->pressureLevels()->count());
    }

    /**
     * Test master edit page shows pressure levels
     */
    public function test_master_edit_page_shows_pressure_levels(): void
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

        $master->pressureLevels()->attach($light);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.masters.edit', $master));

        $response->assertStatus(200);
        // The pressure levels should be in the response data
        $response->assertViewHas('pressureLevels');
    }

    /**
     * Test master can be updated with new pressure levels
     */
    public function test_master_can_be_updated_with_new_pressure_levels(): void
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

        $heavy = PressureLevel::create([
            'slug' => 'heavy',
            'name' => ['uz' => 'Kuchli'],
            'status' => true,
        ]);

        // Initially attach light and medium
        $master->pressureLevels()->attach([$light->id, $medium->id]);

        $response = $this->actingAs($this->admin)
            ->patch(route('admin.masters.update', $master), [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'phone' => '+998901234567',
                'email' => 'john@example.com',
                'password' => '',
                'gender' => 'male',
                'birth_date' => '1990-01-01',
                'experience_years' => 5,
                'status' => true,
                'service_types' => [],
                'oils' => [],
                'pressure_levels' => [$medium->id, $heavy->id],
                'uz' => ['bio' => 'Updated bio'],
                'ru' => ['bio' => 'Updated bio'],
                'en' => ['bio' => 'Updated bio'],
            ]);

        $response->assertRedirect(route('admin.masters.index'));

        $master->refresh();
        $this->assertEquals(2, $master->pressureLevels()->count());
        $this->assertFalse($master->pressureLevels()->where('slug', 'light')->exists());
        $this->assertTrue($master->pressureLevels()->where('slug', 'medium')->exists());
        $this->assertTrue($master->pressureLevels()->where('slug', 'heavy')->exists());
    }

    /**
     * Test master pressure levels are preserved in edit data
     */
    public function test_master_pressure_levels_are_returned_in_edit_data(): void
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

        $response = $this->actingAs($this->admin)
            ->get(route('admin.masters.edit', $master));

        // Verify the master data includes pressure level IDs
        $master_data = $response['master'];
        $this->assertArrayHasKey('pressure_levels', $master_data);
        $this->assertCount(2, $master_data['pressure_levels']);
        $this->assertContains($light->id, $master_data['pressure_levels']);
        $this->assertContains($medium->id, $master_data['pressure_levels']);
    }

    /**
     * Test master validation accepts pressure_levels
     */
    public function test_master_validation_accepts_pressure_levels(): void
    {
        $light = PressureLevel::create([
            'slug' => 'light',
            'name' => ['uz' => 'Yumshoq'],
            'status' => true,
        ]);

        // Invalid pressure level ID
        $response = $this->actingAs($this->admin)
            ->post(route('admin.masters.store'), [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'phone' => '+998901234567',
                'email' => 'john@example.com',
                'password' => 'password123',
                'gender' => 'male',
                'experience_years' => 5,
                'status' => true,
                'service_types' => [],
                'oils' => [],
                'pressure_levels' => [9999], // Non-existent ID
                'uz' => ['bio' => 'Test bio'],
                'ru' => ['bio' => 'Test bio'],
                'en' => ['bio' => 'Test bio'],
            ]);

        $response->assertSessionHasErrors('pressure_levels.0');
    }
}
