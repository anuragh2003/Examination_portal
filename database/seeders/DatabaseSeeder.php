<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create or get Admin User
        User::firstOrCreate(
            ['email' => 'admin@email.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('admin123'),
            ]
        );

        // Create or get Test User
        User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => Hash::make('test1234'),
            ]
        );
    }
}
