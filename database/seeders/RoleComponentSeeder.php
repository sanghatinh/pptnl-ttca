<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleComponentSeeder extends Seeder
{
    public function run()
    {
        $components = [
            'Dashboards',
            'Admin Dashboard',
            'Sales Dashboard',
            'CRM Dashboard',
            'Quản lý hệ thống',
            'Danh sách User',
            'Cấp quyền',
            'Nhóm nhân viên',
            'Profile'
        ];

        foreach ($components as $component) {
            DB::table('components')->insert([
                'name' => $component,
            ]);
        }

        $roleComponents = [
            ['role_id' => 1, 'component_id' => 1, 'can_view' => true],
            ['role_id' => 1, 'component_id' => 2, 'can_view' => true],
            ['role_id' => 1, 'component_id' => 3, 'can_view' => true],
            ['role_id' => 1, 'component_id' => 4, 'can_view' => true],
            ['role_id' => 1, 'component_id' => 5, 'can_view' => true],
            ['role_id' => 1, 'component_id' => 6, 'can_view' => true],
            ['role_id' => 1, 'component_id' => 7, 'can_view' => true],
            ['role_id' => 1, 'component_id' => 8, 'can_view' => true],
            ['role_id' => 1, 'component_id' => 9, 'can_view' => true],
            ['role_id' => 2, 'component_id' => 5, 'can_view' => true],
            ['role_id' => 2, 'component_id' => 6, 'can_view' => true],
            ['role_id' => 2, 'component_id' => 7, 'can_view' => true],
            ['role_id' => 2, 'component_id' => 8, 'can_view' => true],
            ['role_id' => 2, 'component_id' => 9, 'can_view' => true],
        ];

        DB::table('role_component')->insert($roleComponents);
    }
}