<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ModaliteSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('modalites')->insert([
            ['nom' => 'Orale', 'description' => 'Évaluation orale'],
            ['nom' => 'Écrite', 'description' => 'Évaluation écrite'],
            ['nom' => 'Pratique', 'description' => 'Évaluation pratique'],
            ['nom' => 'Savoir-faire', 'description' => 'Évaluation de savoir-faire'],
            ['nom' => 'Oral', 'description' => 'Évaluation orale'],
            ['nom' => 'written', 'description' => 'Évaluation écrite'],
            ['nom' => 'Attitude', 'description' => 'Évaluation pratique'],
            
        ]);
    }
}
