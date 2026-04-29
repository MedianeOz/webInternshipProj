<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Airline;


class AirlineSeeder extends Seeder
{
    
    public function run(): void
    {
        Airline::insert([
            ['name' => "Air Côte d'Ivoire",        'code' => 'HF', 'logo_url' => null],
            ['name' => 'Asky Airlines',            'code' => 'KP', 'logo_url' => null],
            ['name' => 'Air Senegal',              'code' => 'HC', 'logo_url' => null],
            ['name' => 'Arik Air',                 'code' => 'W3', 'logo_url' => null],
            ['name' => 'Air Burkina',              'code' => '2J', 'logo_url' => null],
            ['name' => 'Ceiba Intercontinental',   'code' => 'C2', 'logo_url' => null],
            ['name' => 'Cronos Airlines',          'code' => 'C8', 'logo_url' => null],
        ]);
    }
    
}
