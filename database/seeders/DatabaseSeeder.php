<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed roles and permissions
        $this->call(RoleAndPermissionSeeder::class);

        // ========== CREATE ADMIN USER ==========
        $admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
        ]);
        $admin->assignRole('admin');

        // ========== CREATE DISPATCHER USER ==========
        $dispatcher = User::factory()->create([
            'name' => 'Dispatcher User',
            'email' => 'dispatcher@example.com',
        ]);
        $dispatcher->assignRole('dispatcher');

        // ========== CREATE MASTER USERS ==========
        $master1 = User::factory()->create([
            'name' => 'Jamshid - Mastershunoslik',
            'email' => 'master1@example.com',
        ]);
        $master1->assignRole('master');

        $master2 = User::factory()->create([
            'name' => 'Fatima - Salonistka',
            'email' => 'master2@example.com',
        ]);
        $master2->assignRole('master');

        $master3 = User::factory()->create([
            'name' => 'Abdullayev - Remondi',
            'email' => 'master3@example.com',
        ]);
        $master3->assignRole('master');

        // ========== CREATE CUSTOMER USERS ==========
        $customer1 = User::factory()->create([
            'name' => 'Mustafo - Xaridor',
            'email' => 'customer1@example.com',
        ]);
        $customer1->assignRole('customer');

        $customer2 = User::factory()->create([
            'name' => 'Guli - Xaridor',
            'email' => 'customer2@example.com',
        ]);
        $customer2->assignRole('customer');

        $customer3 = User::factory()->create([
            'name' => 'Aziz - Xaridor',
            'email' => 'customer3@example.com',
        ]);
        $customer3->assignRole('customer');

        // ========== LEGACY ROLES ==========
        $editor = User::factory()->create([
            'name' => 'Editor User',
            'email' => 'editor@example.com',
        ]);
        $editor->assignRole('editor');

        $writer = User::factory()->create([
            'name' => 'Writer User',
            'email' => 'writer@example.com',
        ]);
        $writer->assignRole('writer');

        // ========== CREATE ADDITIONAL TEST USERS ==========
        User::factory(10)->create();
    }
}
