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

        // Seed service types and oils
        $this->call(ServiceTypeSeeder::class);
        $this->call(OilSeeder::class);

        // ========== CREATE ADMIN USER ==========
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            ['name' => 'Admin User', 'password' => bcrypt('password')]
        );
        $admin->syncRoles('admin');

        // ========== CREATE DISPATCHER USER ==========
        $dispatcher = User::firstOrCreate(
            ['email' => 'dispatcher@example.com'],
            ['name' => 'Dispatcher User', 'password' => bcrypt('password')]
        );
        $dispatcher->syncRoles('dispatcher');

        // ========== CREATE MASTER USERS ==========
        $master1 = User::firstOrCreate(
            ['email' => 'master1@example.com'],
            ['name' => 'Jamshid - Mastershunoslik', 'password' => bcrypt('password')]
        );
        $master1->syncRoles('master');

        $master2 = User::firstOrCreate(
            ['email' => 'master2@example.com'],
            ['name' => 'Fatima - Salonistka', 'password' => bcrypt('password')]
        );
        $master2->syncRoles('master');

        $master3 = User::firstOrCreate(
            ['email' => 'master3@example.com'],
            ['name' => 'Abdullayev - Remondi', 'password' => bcrypt('password')]
        );
        $master3->syncRoles('master');

        // ========== CREATE CUSTOMER USERS ==========
        $customer1 = User::firstOrCreate(
            ['email' => 'customer1@example.com'],
            ['name' => 'Mustafo - Xaridor', 'password' => bcrypt('password')]
        );
        $customer1->syncRoles('customer');

        $customer2 = User::firstOrCreate(
            ['email' => 'customer2@example.com'],
            ['name' => 'Guli - Xaridor', 'password' => bcrypt('password')]
        );
        $customer2->syncRoles('customer');

        $customer3 = User::firstOrCreate(
            ['email' => 'customer3@example.com'],
            ['name' => 'Aziz - Xaridor', 'password' => bcrypt('password')]
        );
        $customer3->syncRoles('customer');

        // ========== LEGACY ROLES ==========
        $editor = User::firstOrCreate(
            ['email' => 'editor@example.com'],
            ['name' => 'Editor User', 'password' => bcrypt('password')]
        );
        $editor->syncRoles('editor');

        $writer = User::firstOrCreate(
            ['email' => 'writer@example.com'],
            ['name' => 'Writer User', 'password' => bcrypt('password')]
        );
        $writer->syncRoles('writer');
    }
}
