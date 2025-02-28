<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@gmail.com',
            'role' => 'admin',
            'password' => bcrypt('test123456'),
        ]);

        $this->call([RoomSeeder::class, BedSeeder::class]);
    }
}
