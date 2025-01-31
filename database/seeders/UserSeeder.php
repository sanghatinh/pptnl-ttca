<?php

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        User::create([
            'username' => 'admin',
            'password' => Hash::make('password'),
            'full_name' => 'Admin User',
            'position' => 'Administrator',
            'department' => 'IT',
            'email' => 'admin@example.com',
            'role_id' => 1,
        ]);
    }
}
