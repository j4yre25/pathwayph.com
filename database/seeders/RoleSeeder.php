<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;


class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $role_peso = Role::firstOrCreate(['name' => 'peso']);
        $role_institution = Role::firstOrCreate(['name' => 'institution']);
        $role_company = Role::firstOrCreate(['name' => 'company']);
        $role_graduate = Role::firstOrCreate(['name' => 'graduate']);

        $permission_manage_users = Permission::firstOrCreate(['name' => 'manage users']);
        $permission_manage_approval_graduate = Permission::firstOrCreate(['name' => 'manage approval graduate']);
        $permission_manage_institution = Permission::firstOrCreate(['name' => 'manage institution']);
        $permission_manage_graduate = Permission::firstOrCreate(['name' => 'manage graduate']);
        $permission_post_jobs = Permission::firstOrCreate(['name' => 'post jobs']);
        $permission_edit_jobs = Permission::firstOrCreate(['name' => 'edit jobs']);
        $permission_view_jobs = Permission::firstOrCreate(['name' => 'view jobs']);
        $permission_view_jobs = Permission::firstOrCreate(['name' => 'delete jobs']);
        $permission_add_sectors = Permission::FirstorCreate(['name' => 'add sectors']);
        $permission_update_sectors = Permission::FirstorCreate(['name' => 'update sectors']);
        $permission_delete_sectors = Permission::FirstorCreate(['name' => 'delete sectors']);
        $permission_view_sectors = Permission::FirstorCreate(['name' => 'view sectors']);

        $permissions_peso = [$permission_view_sectors, $permission_manage_users, $permission_post_jobs, $permission_edit_jobs, $permission_view_jobs, $permission_add_sectors, $permission_update_sectors,  $permission_delete_sectors];
        $permissions_institution = [$permission_manage_approval_graduate,  $permission_manage_institution, $permission_manage_graduate,  $permission_post_jobs, $permission_edit_jobs, $permission_view_jobs];
        $permissions_company = [$permission_manage_users, $permission_post_jobs, $permission_edit_jobs, $permission_view_jobs];

        $role_peso->syncPermissions($permissions_peso);
        $role_institution->syncPermissions($permissions_institution);
        $role_company->syncPermissions($permissions_company);

        $role_graduate->givePermissionTo($permission_view_jobs);
        $role_peso->givePermissionTo($permission_manage_users);

    
    }
}