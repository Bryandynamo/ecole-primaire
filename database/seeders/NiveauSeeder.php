<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NiveauSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('niveaux')->insert([
            ['nom' => 'Cours d’Initiation'],
            ['nom' => 'Cours Préparatoire'],
            ['nom' => 'Cours Élémentaire 1'],
            ['nom' => 'Cours Élémentaire 2'],
            ['nom' => 'Cours Moyen 1'],
            ['nom' => 'Cours Moyen 2'],
        ]);
    }
}
