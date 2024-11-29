<?php

namespace Database\Seeders;

use App\Models\Area;
use App\Models\Group;
use App\Models\Position;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;

class UserRolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Permissions
        Permission::create(['name' => 'view role']);
        Permission::create(['name' => 'create role']);
        Permission::create(['name' => 'update role']);
        Permission::create(['name' => 'delete role']);

        Permission::create(['name' => 'view permission']);
        Permission::create(['name' => 'create permission']);
        Permission::create(['name' => 'update permission']);
        Permission::create(['name' => 'delete permission']);

        Permission::create(['name' => 'view user']);
        Permission::create(['name' => 'create user']);
        Permission::create(['name' => 'update user']);
        Permission::create(['name' => 'delete user']);

        Area::create(['ten' => 'Area 1', 'ma' => 'area-1', 'mota' => 'This is area 1']);
        Area::create(['ten' => 'Area 2', 'ma' => 'area-2', 'mota' => 'This is area 2']);

        Position::create(['ten' => 'Position 1', 'ma' => 'position-1', 'mota' => 'This is position 1']);
        Position::create(['ten' => 'position 2', 'ma' => 'position-2', 'mota' => 'This is position 2']);

        Group::create(['ten' => 'Group 1', 'mota' => 'This is group 1']);
        Group::create(['ten' => 'Group 2', 'mota' => 'This is group 2']);
        // Create Roles
        $superAdminRole = Role::create(['name' => 'super-admin']); //as super-admin
        $adminRole = Role::create(['name' => 'admin']);
        $editorRole = Role::create(['name' => 'editor']);

        // Lets give all permission to super-admin role.
        $allPermissionNames = Permission::pluck('name')->toArray();

        $superAdminRole->givePermissionTo($allPermissionNames);

        // Let's give few permissions to admin role.
        $adminRole->givePermissionTo(['create role', 'view role', 'update role']);
        $adminRole->givePermissionTo(['create permission', 'view permission']);
        $adminRole->givePermissionTo(['create user', 'view user', 'update user']);


        // Let's Create User and assign Role to it.

        $superAdminUser = User::firstOrCreate([
            'email' => 'superadmin@gmail.com',
        ], [
            'name' => 'Super Admin',
            'password' => Hash::make('tathanh200320'),
        ]);

        $superAdminUser->assignRole($superAdminRole);


        $adminUser = User::firstOrCreate([
            'email' => 'admin@gmail.com'
        ], [
            'name' => 'Admin',
            'password' => Hash::make('tathanh200320'),
        ]);

        $adminUser->assignRole($adminRole);


        $editorUser = User::firstOrCreate([
            'email' => 'tathanh200320@gmail.com',
        ], [
            'name' => 'editor',
            'password' => Hash::make('tathanh200320'),
        ]);

        $editorUser->assignRole($editorRole);
    }
}