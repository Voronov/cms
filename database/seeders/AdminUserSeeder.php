<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Super Admin (Can approve others - logically, though currently all approved users can)
        User::firstOrCreate(
            ['email' => 'superadmin@example.com'],
            [
                'name' => 'Super Admin',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'remember_token' => Str::random(10),
                'is_approved' => true,
            ]
        );

        // Simple Admin (Just another approved user for now)
        User::firstOrCreate(
            ['email' => 'simpleadmin@example.com'],
            [
                'name' => 'Simple Admin',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'remember_token' => Str::random(10),
                'is_approved' => true,
            ]
        );
    }
}
