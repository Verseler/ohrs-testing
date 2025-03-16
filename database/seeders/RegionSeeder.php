<?php

namespace Database\Seeders;

use App\Models\Region;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RegionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $regions = [
            'NCR',
            'CAR',
            '1',
            '2',
            '3',
            '4A (CALABARZON)',
            '4B (MIMAROPA)',
            '5',
            '6',
            '7',
            '8',
            '9',
            '10',
            '11',
            '12',
            '13',
        ];

        foreach ($regions as $region) {
            Region::create(['name' => $region]);
        }
    }
}
