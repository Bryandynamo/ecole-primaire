<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserDemoSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'enseignant.demo@example.com'],
            [
                'name' => 'Enseignant Démo',
                'password' => Hash::make('password'),
            ]
        );
    }
}
