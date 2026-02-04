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
        Permission::create(['name' => 'create posts']);
        Permission::create(['name' => 'edit posts']);
        Permission::create(['name' => 'delete posts']);
        Permission::create(['name' => 'view posts']);
        Permission::create(['name' => 'create categories']);
        Permission::create(['name' => 'edit categories']);
        Permission::create(['name' => 'delete categories']);

        // ========== HOMESERVICE PERMISSIONS ==========
        // Service Management
        Permission::create(['name' => 'create services']);
        Permission::create(['name' => 'edit services']);
        Permission::create(['name' => 'delete services']);
        Permission::create(['name' => 'view services']);
        Permission::create(['name' => 'manage service categories']);

        // Booking Management
        Permission::create(['name' => 'create bookings']);
        Permission::create(['name' => 'edit bookings']);
        Permission::create(['name' => 'view bookings']);
        Permission::create(['name' => 'cancel bookings']);
        Permission::create(['name' => 'assign bookings']);
        Permission::create(['name' => 'change booking status']);
        Permission::create(['name' => 'view all bookings']);

        // Profile Management
        Permission::create(['name' => 'edit own profile']);
        Permission::create(['name' => 'view own profile']);
        Permission::create(['name' => 'edit master profile']);
        Permission::create(['name' => 'view master profile']);

        // Rating & Review
        Permission::create(['name' => 'create ratings']);
        Permission::create(['name' => 'view ratings']);
        Permission::create(['name' => 'manage ratings']);

        // Payment & Transactions
        Permission::create(['name' => 'view payments']);
        Permission::create(['name' => 'process payments']);
        Permission::create(['name' => 'refund payments']);

        // User Management (Admin only)
        Permission::create(['name' => 'manage users']);
        Permission::create(['name' => 'manage masters']);
        Permission::create(['name' => 'manage dispatchers']);
        Permission::create(['name' => 'view user statistics']);

        // System Management (Admin only)
        Permission::create(['name' => 'view admin panel']);
        Permission::create(['name' => 'manage settings']);
        Permission::create(['name' => 'view reports']);
        Permission::create(['name' => 'manage payments']);

        // ========== CREATE ROLES ==========
        // Admin role - Full system access
        $admin = Role::create(['name' => 'admin']);

        // Dispatcher role - Manages bookings and assigns to masters
        $dispatcher = Role::create(['name' => 'dispatcher']);

        // Master role - Service provider
        $master = Role::create(['name' => 'master']);

        // Customer role - Booking services
        $customer = Role::create(['name' => 'customer']);

        // Legacy roles (for backward compatibility)
        $editor = Role::create(['name' => 'editor']);
        $writer = Role::create(['name' => 'writer']);

        // ========== ASSIGN PERMISSIONS TO ROLES ==========

        // ADMIN - All permissions
        $admin->givePermissionTo(Permission::all());

        // DISPATCHER - Manage bookings and assignments
        $dispatcher->givePermissionTo([
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
        $master->givePermissionTo([
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
        $customer->givePermissionTo([
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
        $editor->givePermissionTo([
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
        $writer->givePermissionTo([
            'create posts',
            'edit posts',
            'view posts',
            'view services',
        ]);
    }
}
