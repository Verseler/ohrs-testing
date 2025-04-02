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
            // NCR (National Capital Region)
            [
                'region_id' => '1',
                'name' => 'Regional Executive Office',
                'has_hostel' => false,
            ],
            [
                'region_id' => '1',
                'name' => 'MEO North',
                'has_hostel' => false,
            ],
            [
                'region_id' => '1',
                'name' => 'MEO East',
                'has_hostel' => false,
            ],
            [
                'region_id' => '1',
                'name' => 'MEO West',
                'has_hostel' => false,
            ],
            [
                'region_id' => '1',
                'name' => 'MEO South',
                'has_hostel' => false,
            ],

            // CAR (Cordillera Administrative Region)
            [
                'region_id' => '2',
                'name' => 'Regional Executive Office',
                'has_hostel' => false,
            ],
            [
                'region_id' => '2',
                'name' => 'PENRO Abra',
                'has_hostel' => false,
            ],
            [
                'region_id' => '2',
                'name' => 'CENRO Bangued',
                'has_hostel' => false,
            ],
            [
                'region_id' => '2',
                'name' => 'CENRO Lagangilang',
                'has_hostel' => false,
            ],
            [
                'region_id' => '2',
                'name' => 'PENRO Apayao',
                'has_hostel' => false,
            ],
            [
                'region_id' => '2',
                'name' => 'CENRO Calanasan',
                'has_hostel' => false,
            ],
            [
                'region_id' => '2',
                'name' => 'CENRO Conner',
                'has_hostel' => false,
            ],
            [
                'region_id' => '2',
                'name' => 'PENRO Benguet',
                'has_hostel' => false,
            ],
            [
                'region_id' => '2',
                'name' => 'CENRO Baguio',
                'has_hostel' => false,
            ],
            [
                'region_id' => '2',
                'name' => 'CENRO Baguias',
                'has_hostel' => false,
            ],
            [
                'region_id' => '2',
                'name' => 'PENRO Ifugao',
                'has_hostel' => false,
            ],
            [
                'region_id' => '2',
                'name' => 'CENRO Alfonso Lista',
                'has_hostel' => false,
            ],
            [
                'region_id' => '2',
                'name' => 'CENRO Lamut',
                'has_hostel' => false,
            ],
            [
                'region_id' => '2',
                'name' => 'PENRO Kalinga',
                'has_hostel' => false,
            ],
            [
                'region_id' => '2',
                'name' => 'CENRO Pinukpuk',
                'has_hostel' => false,
            ],
            [
                'region_id' => '2',
                'name' => 'CENRO Tabuk',
                'has_hostel' => false,
            ],
            [
                'region_id' => '2',
                'name' => 'PENRO Mt. Province',
                'has_hostel' => false,
            ],
            [
                'region_id' => '2',
                'name' => 'CENRO Paracelis',
                'has_hostel' => false,
            ],
            [
                'region_id' => '2',
                'name' => 'CENRO Sabangan',
                'has_hostel' => false,
            ],

            // Region 1
            [
                'region_id' => '3',
                'name' => 'Regional Executive Office',
                'has_hostel' => false,
            ],
            [
                'region_id' => '3',
                'name' => 'PENRO Ilocos Norte',
                'has_hostel' => false,
            ],
            [
                'region_id' => '3',
                'name' => 'CENRO Laoag',
                'has_hostel' => false,
            ],
            [
                'region_id' => '3',
                'name' => 'CENRO Bangui',
                'has_hostel' => false,
            ],
            [
                'region_id' => '3',
                'name' => 'PENRO Ilocos Sur',
                'has_hostel' => false,
            ],
            [
                'region_id' => '3',
                'name' => 'CENRO Bantay',
                'has_hostel' => false,
            ],
            [
                'region_id' => '3',
                'name' => 'CENRO Tagudin',
                'has_hostel' => false,
            ],
            [
                'region_id' => '3',
                'name' => 'PENRO La Union',
                'has_hostel' => false,
            ],
            [
                'region_id' => '3',
                'name' => 'PENRO Pangasinan',
                'has_hostel' => false,
            ],
            [
                'region_id' => '3',
                'name' => 'CENRO Dagupan',
                'has_hostel' => false,
            ],
            [
                'region_id' => '3',
                'name' => 'CENRO Urdaneta',
                'has_hostel' => false,
            ],
            [
                'region_id' => '3',
                'name' => 'CENRO Alaminos',
                'has_hostel' => false,
            ],

            // Region 2
            [
                'region_id' => '4',
                'name' => 'Regional Executive Office',
                'has_hostel' => false,
            ],
            [
                'region_id' => '4',
                'name' => 'PENRO Batanes',
                'has_hostel' => false,
            ],
            [
                'region_id' => '4',
                'name' => 'PENRO Cagayan',
                'has_hostel' => false,
            ],
            [
                'region_id' => '4',
                'name' => 'CENRO Alcala',
                'has_hostel' => false,
            ],
            [
                'region_id' => '4',
                'name' => 'CENRO Solana',
                'has_hostel' => false,
            ],
            [
                'region_id' => '4',
                'name' => 'CENRO Aparri',
                'has_hostel' => false,
            ],
            [
                'region_id' => '4',
                'name' => 'CENRO Sanchez Mira',
                'has_hostel' => false,
            ],
            [
                'region_id' => '4',
                'name' => 'PENRO Isabela',
                'has_hostel' => false,
            ],
            [
                'region_id' => '4',
                'name' => 'CENRO Cabagan',
                'has_hostel' => false,
            ],
            [
                'region_id' => '4',
                'name' => 'CENRO Palanan',
                'has_hostel' => false,
            ],
            [
                'region_id' => '4',
                'name' => 'CENRO Cauayan',
                'has_hostel' => false,
            ],
            [
                'region_id' => '4',
                'name' => 'CENRO Naguilian',
                'has_hostel' => false,
            ],
            [
                'region_id' => '4',
                'name' => 'CENRO San Isidro',
                'has_hostel' => false,
            ],
            [
                'region_id' => '4',
                'name' => 'PENRO Nueva Vizcaya',
                'has_hostel' => false,
            ],
            [
                'region_id' => '4',
                'name' => 'CENRO Aritao',
                'has_hostel' => false,
            ],
            [
                'region_id' => '4',
                'name' => 'CENRO Dupax',
                'has_hostel' => false,
            ],
            [
                'region_id' => '4',
                'name' => 'PENRO Quirino',
                'has_hostel' => false,
            ],
            [
                'region_id' => '4',
                'name' => 'CENRO Diffun',
                'has_hostel' => false,
            ],
            [
                'region_id' => '4',
                'name' => 'CENRO Nagtipunan',
                'has_hostel' => false,
            ],

            // Region 3
            [
                'region_id' => '5',
                'name' => 'Regional Executive Office',
                'has_hostel' => false,
            ],
            [
                'region_id' => '5',
                'name' => 'PENRO Aurora',
                'has_hostel' => false,
            ],
            [
                'region_id' => '5',
                'name' => 'CENRO Casiguran',
                'has_hostel' => false,
            ],
            [
                'region_id' => '5',
                'name' => 'CENRO Dingalan',
                'has_hostel' => false,
            ],
            [
                'region_id' => '5',
                'name' => 'PENRO Bataan',
                'has_hostel' => false,
            ],
            [
                'region_id' => '5',
                'name' => 'CENRO Bagac',
                'has_hostel' => false,
            ],
            [
                'region_id' => '5',
                'name' => 'CENRO Dinalupihan',
                'has_hostel' => false,
            ],
            [
                'region_id' => '5',
                'name' => 'PENRO Bulacan',
                'has_hostel' => false,
            ],
            [
                'region_id' => '5',
                'name' => 'CENRO Baliuag',
                'has_hostel' => false,
            ],
            [
                'region_id' => '5',
                'name' => 'CENRO Guiguinto',
                'has_hostel' => false,
            ],
            [
                'region_id' => '5',
                'name' => 'PENRO Nueva Ecija',
                'has_hostel' => false,
            ],
            [
                'region_id' => '5',
                'name' => 'CENRO Cabanatuan City',
                'has_hostel' => false,
            ],
            [
                'region_id' => '5',
                'name' => 'CENRO Muñoz',
                'has_hostel' => false,
            ],
            [
                'region_id' => '5',
                'name' => 'PENRO Pampanga',
                'has_hostel' => false,
            ],
            [
                'region_id' => '5',
                'name' => 'PENRO Tarcal',
                'has_hostel' => false,
            ],
            [
                'region_id' => '5',
                'name' => 'CENRO Camiling',
                'has_hostel' => false,
            ],
            [
                'region_id' => '5',
                'name' => 'CENRO Capas',
                'has_hostel' => false,
            ],
            [
                'region_id' => '5',
                'name' => 'PENRO Zamales',
                'has_hostel' => false,
            ],
            [
                'region_id' => '5',
                'name' => 'CENRO Masinloc',
                'has_hostel' => false,
            ],
            [
                'region_id' => '5',
                'name' => 'CENRO Olangapo City',
                'has_hostel' => false,
            ],

            // 9
            [
                'region_id' => '6',
                'name' => 'Regional Executive Office',
                'has_hostel' => false,
            ],
            [
                'region_id' => '6',
                'name' => 'PENRO Cavite',
                'has_hostel' => false,
            ],
            [
                'region_id' => '6',
                'name' => 'PENRO Laguna',
                'has_hostel' => false,
            ],
            [
                'region_id' => '6',
                'name' => 'CENRO Sta. Cruz',
                'has_hostel' => false,
            ],
            [
                'region_id' => '6',
                'name' => 'PENRO Batangas',
                'has_hostel' => false,
            ],
            [
                'region_id' => '6',
                'name' => 'CENRO Lipa City',
                'has_hostel' => false,
            ],
            [
                'region_id' => '6',
                'name' => 'CENRO Calaca',
                'has_hostel' => false,
            ],
            [
                'region_id' => '6',
                'name' => 'PENRO Rizal',
                'has_hostel' => false,
            ],
            [
                'region_id' => '6',
                'name' => 'PENRO Quezon',
                'has_hostel' => false,
            ],
            [
                'region_id' => '6',
                'name' => 'CENRO Calauag',
                'has_hostel' => false,
            ],
            [
                'region_id' => '6',
                'name' => 'CENRO Catanauan',
                'has_hostel' => false,
            ],
            [
                'region_id' => '6',
                'name' => 'CENRO Tayabas',
                'has_hostel' => false,
            ],
            [
                'region_id' => '6',
                'name' => 'CENRO Real',
                'has_hostel' => false,
            ],

            // 10
            [
                'region_id' => '7',
                'name' => 'Regional Executive Office',
                'has_hostel' => false,
            ],
            [
                'region_id' => '7',
                'name' => 'PENRO Occidental Mindoro',
                'has_hostel' => false,
            ],
            [
                'region_id' => '7',
                'name' => 'CENRO Sablayan',
                'has_hostel' => false,
            ],
            [
                'region_id' => '7',
                'name' => 'CENRO San Jose',
                'has_hostel' => false,
            ],
            [
                'region_id' => '7',
                'name' => 'PENRO Oriental Mindoro',
                'has_hostel' => false,
            ],
            [
                'region_id' => '7',
                'name' => 'CENRO Roxas',
                'has_hostel' => false,
            ],
            [
                'region_id' => '7',
                'name' => 'PENRO Marinduque',
                'has_hostel' => false,
            ],
            [
                'region_id' => '7',
                'name' => 'PENRO Palawan',
                'has_hostel' => false,
            ],
            [
                'region_id' => '7',
                'name' => 'CENRO Brooke\'s Point',
                'has_hostel' => false,
            ],
            [
                'region_id' => '7',
                'name' => 'CENRO Coron',
                'has_hostel' => false,
            ],
            [
                'region_id' => '7',
                'name' => 'CENRO Puerto Princesa',
                'has_hostel' => false,
            ],
            [
                'region_id' => '7',
                'name' => 'CENRO Quezon',
                'has_hostel' => false,
            ],
            [
                'region_id' => '7',
                'name' => 'CENRO Roxas',
                'has_hostel' => false,
            ],
            [
                'region_id' => '7',
                'name' => 'CENRO Taytay',
                'has_hostel' => false,
            ],

            // Region 5
            [
                'region_id' => '8',
                'name' => 'Regional Executive Office',
                'has_hostel' => false,
            ],
            [
                'region_id' => '8',
                'name' => 'PENRO Albay',
                'has_hostel' => false,
            ],
            [
                'region_id' => '8',
                'name' => 'CENRO Guinobatan',
                'has_hostel' => false,
            ],
            [
                'region_id' => '8',
                'name' => 'PENRO Camarines Norte',
                'has_hostel' => false,
            ],
            [
                'region_id' => '8',
                'name' => 'PENRO Camarines Sur',
                'has_hostel' => false,
            ],
            [
                'region_id' => '8',
                'name' => 'CENRO Iriga City',
                'has_hostel' => false,
            ],
            [
                'region_id' => '8',
                'name' => 'CENRO Goa',
                'has_hostel' => false,
            ],
            [
                'region_id' => '8',
                'name' => 'CENRO Sipocot',
                'has_hostel' => false,
            ],
            [
                'region_id' => '8',
                'name' => 'CENRO Mobo',
                'has_hostel' => false,
            ],
            [
                'region_id' => '8',
                'name' => 'CENRO San Jacinto',
                'has_hostel' => false,
            ],
            [
                'region_id' => '8',
                'name' => 'PENRO Catanduanes',
                'has_hostel' => false,
            ],
            [
                'region_id' => '8',
                'name' => 'PENRO Masbate',
                'has_hostel' => false,
            ],
            [
                'region_id' => '8',
                'name' => 'PENRO Sorsogon',
                'has_hostel' => false,
            ],

            // Region 6
            [
                'region_id' => '9',
                'name' => 'Regional Executive Office',
                'has_hostel' => false,
            ],
            [
                'region_id' => '9',
                'name' => 'PENRO Aklan',
                'has_hostel' => false,
            ],
            [
                'region_id' => '9',
                'name' => 'CENRO Boracay',
                'has_hostel' => false,
            ],
            [
                'region_id' => '9',
                'name' => 'PENRO Ilo-Ilo',
                'has_hostel' => false,
            ],
            [
                'region_id' => '9',
                'name' => 'CENRO Guimbal',
                'has_hostel' => false,
            ],
            [
                'region_id' => '9',
                'name' => 'CENRO Barotac Nuevo',
                'has_hostel' => false,
            ],
            [
                'region_id' => '9',
                'name' => 'CENRO Sara',
                'has_hostel' => false,
            ],
            [
                'region_id' => '9',
                'name' => 'PENRO Antique',
                'has_hostel' => false,
            ],
            [
                'region_id' => '9',
                'name' => 'CENRO Belision',
                'has_hostel' => false,
            ],
            [
                'region_id' => '9',
                'name' => 'CENRO Culasi',
                'has_hostel' => false,
            ],
            [
                'region_id' => '9',
                'name' => 'PENRO Guimaras',
                'has_hostel' => false,
            ],
            [
                'region_id' => '9',
                'name' => 'PENRO Negros Occidental',
                'has_hostel' => false,
            ],
            [
                'region_id' => '9',
                'name' => 'CENRO Bago City',
                'has_hostel' => false,
            ],
            [
                'region_id' => '9',
                'name' => 'CENRO Cadiz City',
                'has_hostel' => false,
            ],
            [
                'region_id' => '9',
                'name' => 'CENRO Kabankalan City',
                'has_hostel' => false,
            ],
            [
                'region_id' => '9',
                'name' => 'PENRO Capiz',
                'has_hostel' => false,
            ],
            [
                'region_id' => '9',
                'name' => 'CENRO Mambusao',
                'has_hostel' => false,
            ],

            // Region 7
            [
                'region_id' => '10',
                'name' => 'Regional Executive Office',
                'has_hostel' => false,
            ],
            [
                'region_id' => '10',
                'name' => 'PENRO Cebu',
                'has_hostel' => false,
            ],
            [
                'region_id' => '10',
                'name' => 'CENRO Cebu City',
                'has_hostel' => false,
            ],
            [
                'region_id' => '10',
                'name' => 'CENRO Argao',
                'has_hostel' => false,
            ],
            [
                'region_id' => '10',
                'name' => 'PENRO Bohol',
                'has_hostel' => false,
            ],
            [
                'region_id' => '10',
                'name' => 'CENRO Tagbilaran City',
                'has_hostel' => false,
            ],
            [
                'region_id' => '10',
                'name' => 'CENRO Talibon',
                'has_hostel' => false,
            ],
            [
                'region_id' => '10',
                'name' => 'PENRO Negros Oriental',
                'has_hostel' => false,
            ],
            [
                'region_id' => '10',
                'name' => 'CENRO Dumaguete City',
                'has_hostel' => false,
            ],
            [
                'region_id' => '10',
                'name' => 'CENRO Ayungon',
                'has_hostel' => false,
            ],
            [
                'region_id' => '10',
                'name' => 'CENRO Siquijor',
                'has_hostel' => false,
            ],

            // Region 8
            [
                'region_id' => '11',
                'name' => 'Regional Executive Office',
                'has_hostel' => false,
            ],
            [
                'region_id' => '11',
                'name' => 'PENRO Leyte',
                'has_hostel' => false,
            ],
            [
                'region_id' => '11',
                'name' => 'CENRO Palo',
                'has_hostel' => false,
            ],
            [
                'region_id' => '11',
                'name' => 'CENRO Baybay',
                'has_hostel' => false,
            ],
            [
                'region_id' => '11',
                'name' => 'CENRO Ormoc',
                'has_hostel' => false,
            ],
            [
                'region_id' => '11',
                'name' => 'PENRO Southern Leyte',
                'has_hostel' => false,
            ],
            [
                'region_id' => '11',
                'name' => 'CENRO Maasin',
                'has_hostel' => false,
            ],
            [
                'region_id' => '11',
                'name' => 'CENRO San Juan',
                'has_hostel' => false,
            ],
            [
                'region_id' => '11',
                'name' => 'PENRO Samar',
                'has_hostel' => false,
            ],
            [
                'region_id' => '11',
                'name' => 'CENRO Centro Catbalogan',
                'has_hostel' => false,
            ],
            [
                'region_id' => '11',
                'name' => 'CENRO Sta. Rita',
                'has_hostel' => false,
            ],
            [
                'region_id' => '11',
                'name' => 'PENRO Eastern Samar',
                'has_hostel' => false,
            ],
            [
                'region_id' => '11',
                'name' => 'CENRO Borongan',
                'has_hostel' => false,
            ],
            [
                'region_id' => '11',
                'name' => 'CENRO Dolores',
                'has_hostel' => false,
            ],
            [
                'region_id' => '11',
                'name' => 'PENRO Northern Samar',
                'has_hostel' => false,
            ],
            [
                'region_id' => '11',
                'name' => 'CENRO Catarman',
                'has_hostel' => false,
            ],
            [
                'region_id' => '11',
                'name' => 'CENRO Pambujan',
                'has_hostel' => false,
            ],
            [
                'region_id' => '11',
                'name' => 'PENRO Biliran',
                'has_hostel' => false,
            ],

            // Region 9
            [
                'region_id' => '12',
                'name' => 'Regional Executive Office',
                'has_hostel' => false,
            ],
            [
                'region_id' => '12',
                'name' => 'PENRO Zamboanga del Sur',
                'has_hostel' => false,
            ],
            [
                'region_id' => '12',
                'name' => 'CENRO Guipos',
                'has_hostel' => false,
            ],
            [
                'region_id' => '12',
                'name' => 'CENRO Ramon Magsaysay',
                'has_hostel' => false,
            ],
            [
                'region_id' => '12',
                'name' => 'PENRO Zamboanga del Norte',
                'has_hostel' => false,
            ],
            [
                'region_id' => '12',
                'name' => 'CENRO Piñan',
                'has_hostel' => false,
            ],
            [
                'region_id' => '12',
                'name' => 'CENRO Liloy',
                'has_hostel' => false,
            ],
            [
                'region_id' => '12',
                'name' => 'CENRO Manukan',
                'has_hostel' => false,
            ],
            [
                'region_id' => '12',
                'name' => 'CENRO Siocon',
                'has_hostel' => false,
            ],
            [
                'region_id' => '12',
                'name' => 'PENRO Zamboanga Sibugay',
                'has_hostel' => false,
            ],
            [
                'region_id' => '12',
                'name' => 'CENRO Imelda',
                'has_hostel' => false,
            ],
            [
                'region_id' => '12',
                'name' => 'CENRO Kabasalan',
                'has_hostel' => false,
            ],
            [
                'region_id' => '12',
                'name' => 'CENRO Zamboanga City',
                'has_hostel' => false,
            ],

            // Region 10
            [
                'region_id' => '13',
                'name' => 'Regional Executive Office',
                'has_hostel' => true,
            ],
            [
                'region_id' => '13',
                'name' => 'PENRO Bukidnon',
                'has_hostel' => false,
            ],
            [
                'region_id' => '13',
                'name' => 'CENRO Manolo Fortich',
                'has_hostel' => false,
            ],
            [
                'region_id' => '13',
                'name' => 'CENRO Valencia',
                'has_hostel' => false,
            ],
            [
                'region_id' => '13',
                'name' => 'CENRO Don Carlos',
                'has_hostel' => false,
            ],
            [
                'region_id' => '13',
                'name' => 'CENRO Talakag',
                'has_hostel' => false,
            ],
            [
                'region_id' => '13',
                'name' => 'PENRO Misamis Occidental',
                'has_hostel' => false,
            ],
            [
                'region_id' => '13',
                'name' => 'CENRO Ozamis',
                'has_hostel' => false,
            ],
            [
                'region_id' => '13',
                'name' => 'CENRO Oroquieta',
                'has_hostel' => false,
            ],
            [
                'region_id' => '13',
                'name' => 'PENRO Misamis Oriental',
                'has_hostel' => false,
            ],
            [
                'region_id' => '13',
                'name' => 'CENRO Initiao',
                'has_hostel' => false,
            ],
            [
                'region_id' => '13',
                'name' => 'CENRO Gingoog',
                'has_hostel' => false,
            ],
            [
                'region_id' => '13',
                'name' => 'PENRO Camiguin',
                'has_hostel' => false,
            ],
            [
                'region_id' => '13',
                'name' => 'PENRO Lanao del Norte',
                'has_hostel' => false,
            ],
            [
                'region_id' => '13',
                'name' => 'CENRO Iligan',
                'has_hostel' => false,
            ],
            [
                'region_id' => '13',
                'name' => 'CENRO Kalambugan',
                'has_hostel' => false,
            ],

            // Region 11
            [
                'region_id' => '14',
                'name' => 'Regional Executive Office',
                'has_hostel' => false,
            ],
            [
                'region_id' => '14',
                'name' => 'PENRO Davao Oriental',
                'has_hostel' => false,
            ],
            [
                'region_id' => '14',
                'name' => 'CENRO Mati',
                'has_hostel' => false,
            ],
            [
                'region_id' => '14',
                'name' => 'CENRO Baganga',
                'has_hostel' => false,
            ],
            [
                'region_id' => '14',
                'name' => 'CENRO Manay',
                'has_hostel' => false,
            ],
            [
                'region_id' => '14',
                'name' => 'CENRO Lupon',
                'has_hostel' => false,
            ],
            [
                'region_id' => '14',
                'name' => 'PENRO Davao del Norte',
                'has_hostel' => false,
            ],
            [
                'region_id' => '14',
                'name' => 'CENRO New Corella',
                'has_hostel' => false,
            ],
            [
                'region_id' => '14',
                'name' => 'CENRO Panabo',
                'has_hostel' => false,
            ],
            [
                'region_id' => '14',
                'name' => 'PENRO Davao de Oro',
                'has_hostel' => false,
            ],
            [
                'region_id' => '14',
                'name' => 'CENRO Monkayo',
                'has_hostel' => false,
            ],
            [
                'region_id' => '14',
                'name' => 'CENRO Maco',
                'has_hostel' => false,
            ],
            [
                'region_id' => '14',
                'name' => 'PENRO Davao del Sur',
                'has_hostel' => false,
            ],
            [
                'region_id' => '14',
                'name' => 'CENRO Digos',
                'has_hostel' => false,
            ],
            [
                'region_id' => '14',
                'name' => 'CENRO Malalag',
                'has_hostel' => false,
            ],
            [
                'region_id' => '14',
                'name' => 'CENRO Davao City',
                'has_hostel' => false,
            ],
            [
                'region_id' => '14',
                'name' => 'PENRO Davao Occidental',
                'has_hostel' => false,
            ],

            // Region 12
            [
                'region_id' => '15',
                'name' => 'Regional Executive Office',
                'has_hostel' => false,
            ],
            [
                'region_id' => '15',
                'name' => 'PENRO Cotabato',
                'has_hostel' => false,
            ],
            [
                'region_id' => '15',
                'name' => 'CENRO Midsayap',
                'has_hostel' => false,
            ],
            [
                'region_id' => '15',
                'name' => 'CENRO Matalam',
                'has_hostel' => false,
            ],
            [
                'region_id' => '15',
                'name' => 'PENRO Sultan Kudarat',
                'has_hostel' => false,
            ],
            [
                'region_id' => '15',
                'name' => 'CENRO Tacurong City',
                'has_hostel' => false,
            ],
            [
                'region_id' => '15',
                'name' => 'CENRO Kalamansig',
                'has_hostel' => false,
            ],
            [
                'region_id' => '15',
                'name' => 'PENRO South Cotabato',
                'has_hostel' => false,
            ],
            [
                'region_id' => '15',
                'name' => 'CENRO Banoa',
                'has_hostel' => false,
            ],
            [
                'region_id' => '15',
                'name' => 'CENRO General Santos City',
                'has_hostel' => false,
            ],
            [
                'region_id' => '15',
                'name' => 'PENRO Sarangani',
                'has_hostel' => false,
            ],
            [
                'region_id' => '15',
                'name' => 'CENRO Kiamba',
                'has_hostel' => false,
            ],
            [
                'region_id' => '15',
                'name' => 'CENRO Glan',
                'has_hostel' => false,
            ],

            // Region 13 (Caraga)
            [
                'region_id' => '16',
                'name' => 'Regional Executive Office',
                'has_hostel' => false,
            ],
            [
                'region_id' => '16',
                'name' => 'PENRO Agusan del Norte',
                'has_hostel' => false,
            ],
            [
                'region_id' => '16',
                'name' => 'CENRO Nasipit',
                'has_hostel' => false,
            ],
            [
                'region_id' => '16',
                'name' => 'CENRO Tubay',
                'has_hostel' => false,
            ],
            [
                'region_id' => '16',
                'name' => 'PENRO Agusan del Sur',
                'has_hostel' => false,
            ],
            [
                'region_id' => '16',
                'name' => 'CENRO Bayugan',
                'has_hostel' => false,
            ],
            [
                'region_id' => '16',
                'name' => 'CENRO Loreto',
                'has_hostel' => false,
            ],
            [
                'region_id' => '16',
                'name' => 'CENRO Bunawan',
                'has_hostel' => false,
            ],
            [
                'region_id' => '16',
                'name' => 'CENRO Talacogon',
                'has_hostel' => false,
            ],
            [
                'region_id' => '16',
                'name' => 'PENRO Surigao del Norte',
                'has_hostel' => false,
            ],
            [
                'region_id' => '16',
                'name' => 'CENRO Tubod',
                'has_hostel' => false,
            ],
            [
                'region_id' => '16',
                'name' => 'PENRO Surigao del Sur',
                'has_hostel' => false,
            ],
            [
                'region_id' => '16',
                'name' => 'CENRO Bislig City',
                'has_hostel' => false,
            ],
            [
                'region_id' => '16',
                'name' => 'CENRO Cantilan',
                'has_hostel' => false,
            ],
            [
                'region_id' => '16',
                'name' => 'CENRO Lianga',
                'has_hostel' => false,
            ],
            [
                'region_id' => '16',
                'name' => 'PENRO Province of Dinagat Islands',
                'has_hostel' => false,
            ],
        ];

        foreach ($offices as $office) {
            Office::create([
                'region_id' => $office['region_id'],
                'name' => $office['name'],
                'has_hostel' => $office['has_hostel'],
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }
    }
}
