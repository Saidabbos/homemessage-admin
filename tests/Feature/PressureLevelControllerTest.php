<?php

namespace Tests\Feature;

use App\Models\PressureLevel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PressureLevelControllerTest extends TestCase
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
     * Test pressure level index page is accessible
     */
    public function test_pressure_level_index_page_is_accessible(): void
    {
        PressureLevel::create([
            'slug' => 'light',
            'name' => ['uz' => 'Yumshoq'],
            'status' => true,
            'sort_order' => 1,
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.pressure-levels.index'));

        $response->assertStatus(200);
    }

    /**
     * Test pressure level create page is accessible
     */
    public function test_pressure_level_create_page_is_accessible(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.pressure-levels.create'));

        $response->assertStatus(200);
    }

    /**
     * Test pressure level can be created
     */
    public function test_pressure_level_can_be_created(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.pressure-levels.store'), [
                'slug' => 'light',
                'status' => 1,
                'sort_order' => 1,
                'uz' => [
                    'name' => 'Yumshoq',
                    'description' => 'Yumshoq bosim',
                ],
                'ru' => [
                    'name' => 'Мягкое',
                    'description' => 'Мягкое давление',
                ],
                'en' => [
                    'name' => 'Light',
                    'description' => 'Light pressure',
                ],
            ]);

        $this->assertDatabaseHas('pressure_levels', ['slug' => 'light']);
    }

    /**
     * Test pressure level creation validates required fields
     */
    public function test_pressure_level_creation_validates_required_fields(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.pressure-levels.store'), [
                'slug' => '',
                'uz' => ['name' => ''],
            ]);

        $response->assertSessionHasErrors(['slug', 'uz.name']);
    }

    /**
     * Test pressure level show page is accessible
     */
    public function test_pressure_level_show_page_is_accessible(): void
    {
        $pressureLevel = PressureLevel::create([
            'slug' => 'light',
            'name' => ['uz' => 'Yumshoq'],
            'status' => true,
            'sort_order' => 1,
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.pressure-levels.show', $pressureLevel));

        $response->assertStatus(200);
    }

    /**
     * Test pressure level edit page is accessible
     */
    public function test_pressure_level_edit_page_is_accessible(): void
    {
        $pressureLevel = PressureLevel::create([
            'slug' => 'light',
            'name' => ['uz' => 'Yumshoq'],
            'status' => true,
            'sort_order' => 1,
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.pressure-levels.edit', $pressureLevel));

        $response->assertStatus(200);
    }

    /**
     * Test pressure level can be updated
     */
    public function test_pressure_level_can_be_updated(): void
    {
        $pressureLevel = PressureLevel::create([
            'slug' => 'light',
            'name' => ['uz' => 'Yumshoq'],
            'description' => ['uz' => 'Original description'],
            'status' => true,
            'sort_order' => 1,
        ]);

        $this->actingAs($this->admin)
            ->put(route('admin.pressure-levels.update', $pressureLevel), [
                'slug' => 'light',
                'status' => 0,
                'sort_order' => 2,
                'uz' => [
                    'name' => 'Yumshoq (Updated)',
                    'description' => 'Updated description',
                ],
                'ru' => ['name' => 'Мягкое', 'description' => ''],
                'en' => ['name' => 'Light', 'description' => ''],
            ]);

        $pressureLevel->refresh();
        $this->assertEquals('Yumshoq (Updated)', $pressureLevel->getTranslation('name', 'uz'));
        $this->assertFalse($pressureLevel->status);
        $this->assertEquals(2, $pressureLevel->sort_order);
    }

    /**
     * Test pressure level update validates required fields
     */
    public function test_pressure_level_update_validates_required_fields(): void
    {
        $pressureLevel = PressureLevel::create([
            'slug' => 'light',
            'name' => ['uz' => 'Yumshoq'],
            'status' => true,
            'sort_order' => 1,
        ]);

        $response = $this->actingAs($this->admin)
            ->patch(route('admin.pressure-levels.update', $pressureLevel), [
                'slug' => '',
                'uz' => ['name' => ''],
            ]);

        $response->assertSessionHasErrors(['slug', 'uz.name']);
    }

    /**
     * Test pressure level can be deleted
     */
    public function test_pressure_level_can_be_deleted(): void
    {
        $pressureLevel = PressureLevel::create([
            'slug' => 'light',
            'name' => ['uz' => 'Yumshoq'],
            'status' => true,
            'sort_order' => 1,
        ]);

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.pressure-levels.destroy', $pressureLevel));

        $response->assertRedirect(route('admin.pressure-levels.index'));
        $this->assertDatabaseMissing('pressure_levels', ['id' => $pressureLevel->id]);
    }

    /**
     * Test unauthenticated user cannot access pressure levels
     */
    public function test_unauthenticated_user_cannot_access_pressure_levels(): void
    {
        $response = $this->get(route('admin.pressure-levels.index'));

        $response->assertRedirect(route('login'));
    }

    /**
     * Test non-admin user cannot access pressure levels
     */
    public function test_non_admin_user_cannot_access_pressure_levels(): void
    {
        $user = User::create([
            'name' => 'Regular User',
            'email' => 'user@example.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->actingAs($user)
            ->get(route('admin.pressure-levels.index'));

        $response->assertForbidden();
    }
}
