<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Create Admin
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@angelportal.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
        ]);

        // Create Regular Member
        User::create([
            'name' => 'Regular Member',
            'email' => 'user@angelportal.com',
            'password' => Hash::make('password123'),
            'role' => 'user',
        ]);
    }
}
