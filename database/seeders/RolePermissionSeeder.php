<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // Course permissions
            'view courses',
            'create courses',
            'edit courses',
            'delete courses',

            // Assignment permissions
            'view assignments',
            'create assignments',
            'edit assignments',
            'delete assignments',
            'grade assignments',

            // Material permissions
            'view materials',
            'create materials',
            'edit materials',
            'delete materials',

            // Submission permissions
            'view submissions',
            'create submissions',
            'edit submissions',
            'delete submissions',
            'view all submissions',

            // User management
            'manage users',
            'view users',

            // Sync permissions
            'sync lms',
            'manage lms',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create roles and assign permissions
        $adminRole = Role::create(['name' => 'admin']);
        $adminRole->givePermissionTo(Permission::all());

        $dosenRole = Role::create(['name' => 'dosen']);
        $dosenRole->givePermissionTo([
            'view courses',
            'create courses',
            'edit courses',
            'view assignments',
            'create assignments',
            'edit assignments',
            'delete assignments',
            'grade assignments',
            'view materials',
            'create materials',
            'edit materials',
            'delete materials',
            'view all submissions',
            'sync lms',
        ]);

        $mahasiswaRole = Role::create(['name' => 'mahasiswa']);
        $mahasiswaRole->givePermissionTo([
            'view courses',
            'view assignments',
            'view materials',
            'view submissions',
            'create submissions',
            'edit submissions',
        ]);
    }
}
