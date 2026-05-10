<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@ecommerce.test'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]
        );

        User::firstOrCreate(
            ['email' => 'editor@ecommerce.test'],
            [
                'name' => 'Editor',
                'password' => Hash::make('password'),
                'role' => 'editor',
            ]
        );

        User::firstOrCreate(
            ['email' => 'customer@ecommerce.test'],
            [
                'name' => 'Khách hàng mẫu',
                'password' => Hash::make('password'),
                'role' => 'customer',
            ]
        );
    }
}
