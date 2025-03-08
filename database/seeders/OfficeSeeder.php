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
            ['name' => 'Region 10 Regional Executive Office', 'has_hostel' => true],
            ['name' => 'Region 10 PENRO Bukidnon', 'has_hostel' => false],
            ['name' => 'Region 11 PENRO Davao de Oro', 'has_hostel' => false],
            ['name' => 'Region 11 CENRO Maco', 'has_hostel' => false],
            ['name' => 'Region 11 CENRO Monkayo', 'has_hostel' => false],
            ['name' => 'Region 11 PENRO Davao del Sur', 'has_hostel' => false],
            ['name' => 'Region 11 CENRO Digos', 'has_hostel' => false],
            ['name' => 'Region 11 CENRO Malalag', 'has_hostel' => false],
            ['name' => 'Region 11 CENRO Davao City', 'has_hostel' => false],
            ['name' => 'Region 11 PENRO Davao del Norte', 'has_hostel' => false],
            ['name' => 'Region 11 CENRO New Corella', 'has_hostel' => false],
            ['name' => 'Region 11 CENRO Panabo', 'has_hostel' => false],
            ['name' => 'Region 11 PENRO Davao Occidental', 'has_hostel' => false],
            ['name' => 'Region 11 PENRO Davao Oriental', 'has_hostel' => false],
            ['name' => 'Region 11 CENRO Lupon', 'has_hostel' => false],
            ['name' => 'Region 11 CENRO Mati', 'has_hostel' => false],
            ['name' => 'Region 11 CENRO Manay', 'has_hostel' => false],
            ['name' => 'Region 11 CENRO Baganga', 'has_hostel' => false],
            ['name' => 'Region 12 PENRO Cotabato', 'has_hostel' => false],
            ['name' => 'Region 12 CENRO Matalam', 'has_hostel' => false],
            ['name' => 'Region 12 CENRO Midsayap', 'has_hostel' => false],
            ['name' => 'Region 12 PENRO South Cotabato', 'has_hostel' => false],
            ['name' => 'Region 12 CENRO Banga', 'has_hostel' => false],
            ['name' => 'Region 12 CENRO General Santos City', 'has_hostel' => false],
            ['name' => 'Region 12 PENRO Sarangani', 'has_hostel' => false],
            ['name' => 'Region 12 CENRO Glan', 'has_hostel' => false],
            ['name' => 'Region 12 CENRO Kiamba', 'has_hostel' => false],
            ['name' => 'Region 12 PENRO Sultan Kudarat', 'has_hostel' => false],
            ['name' => 'Region 12 CENRO Kalamansig', 'has_hostel' => false],
            ['name' => 'Region 12 CENRO Tacurong City', 'has_hostel' => false],
            ['name' => 'CARAGA PENRO Agusan del Norte', 'has_hostel' => false],
            ['name' => 'CARAGA CENRO Nasipit', 'has_hostel' => false],
            ['name' => 'CARAGA CENRO Tubay', 'has_hostel' => false],
            ['name' => 'CARAGA PENRO Agusan del Sur', 'has_hostel' => false],
            ['name' => 'CARAGA CENRO Bayugan', 'has_hostel' => false],
            ['name' => 'CARAGA CENRO Bunawan', 'has_hostel' => false],
            ['name' => 'CARAGA CENRO Loreto', 'has_hostel' => false],
            ['name' => 'CARAGA CENRO Talacogon', 'has_hostel' => false],
            ['name' => 'CARAGA PENRO Dinagat Islands', 'has_hostel' => false],
            ['name' => 'CARAGA PENRO Surigao del Norte', 'has_hostel' => false],
            ['name' => 'CARAGA CENRO Tubod', 'has_hostel' => false],
            ['name' => 'CARAGA PENRO Surigao del Sur', 'has_hostel' => false],
            ['name' => 'CARAGA CENRO Bislig', 'has_hostel' => false],
            ['name' => 'CARAGA CENRO Cantilan', 'has_hostel' => false],
            ['name' => 'CARAGA CENRO Lianga', 'has_hostel' => false]
        ];

        foreach ($offices as $office) {
            Office::create([
                'name' => $office['name'],
                'has_hostel' => $office['has_hostel'],
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }
    }
}
