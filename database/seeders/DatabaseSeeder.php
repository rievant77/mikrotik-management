<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with default user only.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@mikrotik.lan'],
            [
                'name' => 'Administrator',
                'password' => Hash::make('password'),
            ]
        );
    }
}
