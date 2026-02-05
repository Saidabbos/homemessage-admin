<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()['cache']->forget('spatie.permission.cache');

        // ========== ADMIN PERMISSIONS ==========
        // These permissions are for content management (posts, categories)
        Permission::firstOrCreate(['name' => 'create posts']);
        Permission::firstOrCreate(['name' => 'edit posts']);
        Permission::firstOrCreate(['name' => 'delete posts']);
        Permission::firstOrCreate(['name' => 'view posts']);
        Permission::firstOrCreate(['name' => 'create categories']);
        Permission::firstOrCreate(['name' => 'edit categories']);
        Permission::firstOrCreate(['name' => 'delete categories']);

        // ========== HOMESERVICE PERMISSIONS ==========
        // Service Management
        Permission::firstOrCreate(['name' => 'create services']);
        Permission::firstOrCreate(['name' => 'edit services']);
        Permission::firstOrCreate(['name' => 'delete services']);
        Permission::firstOrCreate(['name' => 'view services']);
        Permission::firstOrCreate(['name' => 'manage service categories']);

        // Booking Management
        Permission::firstOrCreate(['name' => 'create bookings']);
        Permission::firstOrCreate(['name' => 'edit bookings']);
        Permission::firstOrCreate(['name' => 'view bookings']);
        Permission::firstOrCreate(['name' => 'cancel bookings']);
        Permission::firstOrCreate(['name' => 'assign bookings']);
        Permission::firstOrCreate(['name' => 'change booking status']);
        Permission::firstOrCreate(['name' => 'view all bookings']);

        // Profile Management
        Permission::firstOrCreate(['name' => 'edit own profile']);
        Permission::firstOrCreate(['name' => 'view own profile']);
        Permission::firstOrCreate(['name' => 'edit master profile']);
        Permission::firstOrCreate(['name' => 'view master profile']);

        // Rating & Review
        Permission::firstOrCreate(['name' => 'create ratings']);
        Permission::firstOrCreate(['name' => 'view ratings']);
        Permission::firstOrCreate(['name' => 'manage ratings']);

        // Payment & Transactions
        Permission::firstOrCreate(['name' => 'view payments']);
        Permission::firstOrCreate(['name' => 'process payments']);
        Permission::firstOrCreate(['name' => 'refund payments']);

        // User Management (Admin only)
        Permission::firstOrCreate(['name' => 'manage users']);
        Permission::firstOrCreate(['name' => 'manage masters']);
        Permission::firstOrCreate(['name' => 'manage dispatchers']);
        Permission::firstOrCreate(['name' => 'view user statistics']);

        // System Management (Admin only)
        Permission::firstOrCreate(['name' => 'view admin panel']);
        Permission::firstOrCreate(['name' => 'manage settings']);
        Permission::firstOrCreate(['name' => 'view reports']);
        Permission::firstOrCreate(['name' => 'manage payments']);

        // ========== CREATE ROLES ==========
        // Admin role - Full system access
        $admin = Role::firstOrCreate(['name' => 'admin']);

        // Dispatcher role - Manages bookings and assigns to masters
        $dispatcher = Role::firstOrCreate(['name' => 'dispatcher']);

        // Master role - Service provider
        $master = Role::firstOrCreate(['name' => 'master']);

        // Customer role - Booking services
        $customer = Role::firstOrCreate(['name' => 'customer']);

        // Legacy roles (for backward compatibility)
        $editor = Role::firstOrCreate(['name' => 'editor']);
        $writer = Role::firstOrCreate(['name' => 'writer']);

        // ========== ASSIGN PERMISSIONS TO ROLES ==========

        // ADMIN - All permissions
        $admin->syncPermissions(Permission::all());

        // DISPATCHER - Manage bookings and assignments
        $dispatcher->syncPermissions([
            'view bookings',
            'view all bookings',
            'change booking status',
            'assign bookings',
            'view services',
            'view payments',
            'view ratings',
            'view master profile',
            'view user statistics',
        ]);

        // MASTER - Manage own services and profile
        $master->syncPermissions([
            'create services',
            'edit services',
            'view services',
            'view bookings',
            'edit bookings',
            'change booking status',
            'edit own profile',
            'view own profile',
            'edit master profile',
            'view ratings',
            'create ratings',
            'view payments',
        ]);

        // CUSTOMER - Book services and manage own profile
        $customer->syncPermissions([
            'view services',
            'create bookings',
            'view bookings',
            'cancel bookings',
            'edit own profile',
            'view own profile',
            'create ratings',
            'view ratings',
            'view payments',
        ]);

        // EDITOR - Content management (backward compatibility)
        $editor->syncPermissions([
            'create posts',
            'edit posts',
            'delete posts',
            'view posts',
            'create categories',
            'edit categories',
            'view services',
            'view bookings',
        ]);

        // WRITER - Limited content creation (backward compatibility)
        $writer->syncPermissions([
            'create posts',
            'edit posts',
            'view posts',
            'view services',
        ]);
    }
}
