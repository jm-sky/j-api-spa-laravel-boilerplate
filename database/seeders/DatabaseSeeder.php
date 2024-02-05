<?php

declare(strict_types=1);

namespace Database\Seeders;

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
        \DevMadeIt\Models\User::factory()->create([
            'name' => 'Test User',
            'email' => env('DEFAULT_USER_EMAIL'),
            'password' => Hash::make(env('DEFAULT_USER_PASSWORD', 'password')),
        ]);
    }
}
