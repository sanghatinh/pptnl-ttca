<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run()
    {
        $permissions = ['read', 'create', 'update', 'delete'];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $roles = ['Super Admin', 'Admin', 'Manager', 'User'];

        foreach ($roles as $role) {
            $roleModel = Role::firstOrCreate(['name' => $role]);

            if ($role == 'Super Admin') {
                $roleModel->permissions()->sync(Permission::all());
            }
        }
    }
}