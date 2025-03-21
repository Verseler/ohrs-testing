<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Office;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
       $this->call([
        RegionSeeder::class,
        OfficeSeeder::class,
        RoomSeeder::class,
        BedSeeder::class
    ]);

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@gmail.com',
            'role' => 'admin',
            'office_id' => 175,
            'password' => bcrypt('1010101010'),
        ]);
    }
}
