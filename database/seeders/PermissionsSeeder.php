<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            /* Access Management */
            ['name' => 'Permission Index',  'group_name' => 'Access Management Permissions'],
            ['name' => 'Permission Create', 'group_name' => 'Access Management Permissions'],
            ['name' => 'Permission Update', 'group_name' => 'Access Management Permissions'],
            ['name' => 'Permission Delete', 'group_name' => 'Access Management Permissions'],
            ['name' => 'Role Index',  'group_name' => 'Access Management Permissions'],
            ['name' => 'Role Create', 'group_name' => 'Access Management Permissions'],
            ['name' => 'Role Update', 'group_name' => 'Access Management Permissions'],
            ['name' => 'Role Delete', 'group_name' => 'Access Management Permissions'],

            /* User Management */
            ['name' => 'User Index',  'group_name' => 'User Permissions'],
            ['name' => 'User Create', 'group_name' => 'User Permissions'],
            ['name' => 'User Update', 'group_name' => 'User Permissions'],
            ['name' => 'User Delete', 'group_name' => 'User Permissions'],

            /* Category Management */
            ['name' => 'Category Index',  'group_name' => 'Category Permissions'],
            ['name' => 'Category Create', 'group_name' => 'Category Permissions'],
            ['name' => 'Category Update', 'group_name' => 'Category Permissions'],
            ['name' => 'Category Delete', 'group_name' => 'Category Permissions'],

            /* Course Management */
            ['name' => 'Course Index', 'group_name' => 'Course Permissions'],
            ['name' => 'Course Create', 'group_name' => 'Course Permissions'],
            ['name' => 'Course Update', 'group_name' => 'Course Permissions'],
            ['name' => 'Course Soft Delete', 'group_name' => 'Course Permissions'],
            ['name' => 'Course Force Delete', 'group_name' => 'Course Permissions'],
            ['name' => 'Course Restore', 'group_name' => 'Course Permissions'],
            ['name' => 'Course Toggle Active', 'group_name' => 'Course Permissions'],
            ['name' => 'Course Toggle Registration', 'group_name' => 'Course Permissions'],
            ['name' => 'Course Toggle New', 'group_name' => 'Course Permissions'],

        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission['name'],
                'group_name' => $permission['group_name'],
                'guard_name' => 'api',
            ]);
        }

        $role = Role::firstOrCreate(['guard_name' => 'api', 'name' => 'Super Admin']);

        $allPermissions = Permission::all();
        $role->syncPermissions($allPermissions);
    }
}
