<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Office;
use Carbon\Carbon;

class OfficeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $offices = [
            'Region 10 Regional Executive Office',
            'Region 10 PENRO Bukidnon',
            'Region 11 PENRO Davao de Oro',
            'Region 11 CENRO Maco',
            'Region 11 CENRO Monkayo',
            'Region 11 PENRO Davao del Sur',
            'Region 11 CENRO Digos',
            'Region 11 CENRO Malalag',
            'Region 11 CENRO Davao City',
            'Region 11 PENRO Davao del Norte',
            'Region 11 CENRO New Corella',
            'Region 11 CENRO Panabo',
            'Region 11 PENRO Davao Occidental',
            'Region 11 PENRO Davao Oriental',
            'Region 11 CENRO Lupon',
            'Region 11 CENRO Mati',
            'Region 11 CENRO Manay',
            'Region 11 CENRO Baganga',
            'Region 12 PENRO Cotabato',
            'Region 12 CENRO Matalam',
            'Region 12 CENRO Midsayap',
            'Region 12 PENRO South Cotabato',
            'Region 12 CENRO Banga',
            'Region 12 CENRO General Santos City',
            'Region 12 PENRO Sarangani',
            'Region 12 CENRO Glan',
            'Region 12 CENRO Kiamba',
            'Region 12 PENRO Sultan Kudarat',
            'Region 12 CENRO Kalamansig',
            'Region 12 CENRO Tacurong City',
            'CARAGA PENRO Agusan del Norte',
            'CARAGA CENRO Nasipit',
            'CARAGA CENRO Tubay',
            'CARAGA PENRO Agusan del Sur',
            'CARAGA CENRO Bayugan',
            'CARAGA CENRO Bunawan',
            'CARAGA CENRO Loreto',
            'CARAGA CENRO Talacogon',
            'CARAGA PENRO Dinagat Islands',
            'CARAGA PENRO Surigao del Norte',
            'CARAGA CENRO Tubod',
            'CARAGA PENRO Surigao del Sur',
            'CARAGA CENRO Bislig',
            'CARAGA CENRO Cantilan',
            'CARAGA CENRO Lianga'
        ];

        foreach ($offices as $office) {
            Office::create([
                'name' => $office,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }
    }
}
