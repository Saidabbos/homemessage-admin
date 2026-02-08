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

        // Seed service types, oils, standard items, and pressure levels
        $this->call(ServiceTypeSeeder::class);
        $this->call(OilSeeder::class);
        $this->call(StandardItemSeeder::class);
        $this->call(PressureLevelSeeder::class);

        // Seed settings
        $this->call(SettingSeeder::class);

        // Seed masters with user accounts
        $this->call(MasterSeeder::class);

        // Seed images for service types and masters
        $this->call(ServiceTypeImageSeeder::class);
        $this->call(MasterImageSeeder::class);

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
    }
}
