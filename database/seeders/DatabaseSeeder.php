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
            OfficeSeeder::class
            //RoomSeeder::class,
            //BedSeeder::class
        ]);

        User::factory()->create([
            'name' => 'verselerf_handuman',
            'role' => 'system_admin',
            'office_id' => 176,
            'password' => bcrypt('1010101010'),
        ]);
    }
}
