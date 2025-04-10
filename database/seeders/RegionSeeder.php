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
            ['id' => 1, 'name' => '1'],
            ['id' => 2, 'name' => '2'],
            ['id' => 3, 'name' => '3'],
            ['id' => 4, 'name' => '4A (CALABARZON)'],
            ['id' => 42, 'name' => '4B (MIMAROPA)'],
            ['id' => 5, 'name' => '5'],
            ['id' => 6, 'name' => '6'],
            ['id' => 7, 'name' => '7'],
            ['id' => 8, 'name' => '8'],
            ['id' => 9, 'name' => '9'],
            ['id' => 10, 'name' => '10'],
            ['id' => 11, 'name' => '11'],
            ['id' => 12, 'name' => '12'],
            ['id' => 13, 'name' => '13'],
            ['id' => 14, 'name' => 'NCR'],
            ['id' => 15, 'name' => 'CAR'],
        ];

        foreach ($regions as $region) {
            Region::create($region);
        }
    }
}
