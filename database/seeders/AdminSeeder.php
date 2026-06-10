<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@babivoyage.com'],
            [
                'name' => 'BabiVoyage Admin',
                'email' => 'admin@babivoyage.com',
                'password' => bcrypt('admin123456'),
                'role' => 'admin',
                'phone' => null,
            ]
        );
    }
}
