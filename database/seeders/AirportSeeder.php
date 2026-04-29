<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Airport;


class AirportSeeder extends Seeder
{
    
    public function run(): void
    {
        Airport::insert([
            [
                'name'    => "Félix Houphouët-Boigny International Airport",
                'code'    => 'ABJ',
                'city'    => 'Abidjan',
                'country' => "Côte d'Ivoire",
            ],
            [
                'name'    => 'Kotoka International Airport',
                'code'    => 'ACC',
                'city'    => 'Accra',
                'country' => 'Ghana',
            ],
            [
                'name'    => 'Murtala Muhammed International Airport',
                'code'    => 'LOS',
                'city'    => 'Lagos',
                'country' => 'Nigeria',
            ],
            [
                'name'    => 'Blaise Diagne International Airport',
                'code'    => 'DSS',
                'city'    => 'Dakar',
                'country' => 'Senegal',
            ],
            [
                'name'    => 'Nnamdi Azikiwe International Airport',
                'code'    => 'ABV',
                'city'    => 'Abuja',
                'country' => 'Nigeria',
            ],
            [
                'name'    => 'Maya-Maya Airport',
                'code'    => 'BZV',
                'city'    => 'Brazzaville',
                'country' => 'Congo',
            ],
            [
                'name'    => 'Lungi International Airport',
                'code'    => 'FNA',
                'city'    => 'Freetown',
                'country' => 'Sierra Leone',
            ],
        ]);
    }
}
