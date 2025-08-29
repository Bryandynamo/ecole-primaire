<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SousCompetenceModaliteSeeder extends Seeder
{
    public function run()
    {
        // On vide la table pour éviter les doublons
        DB::table('sous_competence_modalites')->truncate();

        // Exemple de données réelles :
        DB::table('sous_competence_modalites')->insert([
            [
                'sous_competence_id' => 1,
                'modalite_id' => 1,
                'points_max' => 10,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'sous_competence_id' => 1,
                'modalite_id' => 2,
                'points_max' => 10,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'sous_competence_id' => 2,
                'modalite_id' => 1,
                'points_max' => 15,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'sous_competence_id' => 2,
                'modalite_id' => 3,
                'points_max' => 5,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
