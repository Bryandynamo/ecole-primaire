<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CompetencesSeeder extends Seeder
{
    public function run()
    {
        // Création des compétences principales
        $competences = [
            ['nom' => 'COMPETENCE1', 'description' => 'Langues', 'points_max' => 100],
            ['nom' => 'COMPETENCE2', 'description' => 'Sciences', 'points_max' => 60],
            ['nom' => 'COMPETENCE3', 'description' => 'Sciences Sociales', 'points_max' => 40],
            ['nom' => 'COMPETENCE4', 'description' => 'Développement Personnel', 'points_max' => 20],
            ['nom' => 'COMPETENCE5', 'description' => 'TIC', 'points_max' => 20],
            ['nom' => 'COMPETENCE6', 'description' => 'Arts et Sport', 'points_max' => 40],
        ];

        foreach ($competences as $comp) {
            DB::table('competences')->insert([
                'nom' => $comp['nom'],
                'description' => $comp['description'],
                'points_max' => $comp['points_max'],
                'niveau_id' => 1, // Requis par le schéma de la base de données
                'session_id' => 1, // Requis par le schéma de la base de données
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Création des sous-compétences
        $sous_competences = [
            // Compétence 1: Langues
            ['competence_id' => 1, 'nom' => '1A.FRANCAIS', 'points_max' => 40],
            ['competence_id' => 1, 'nom' => '1B.ENGLISH', 'points_max' => 40],
            ['competence_id' => 1, 'nom' => '1C.LANGUE NATIONALITE', 'points_max' => 20],

            // Compétence 2: Sciences
            ['competence_id' => 2, 'nom' => '2A.MATHEMATIQUE', 'points_max' => 30],
            ['competence_id' => 2, 'nom' => '2B.SCIENCES et TECHNOLOGIES', 'points_max' => 30],

            // Compétence 3: Sciences Sociales
            ['competence_id' => 3, 'nom' => '3A.VALEUR SOCIALE(H/G)', 'points_max' => 20],
            ['competence_id' => 3, 'nom' => '3B.VALEURS CITOYENNES', 'points_max' => 20],

            // Compétence 4: Développement Personnel
            ['competence_id' => 4, 'nom' => '4 DeVELOPEMENT PERSONNEL', 'points_max' => 20],

            // Compétence 5: TIC
            ['competence_id' => 5, 'nom' => '5.TIC', 'points_max' => 20],

            // Compétence 6: Arts et Sport
            ['competence_id' => 6, 'nom' => '6A.EPS', 'points_max' => 20],
            ['competence_id' => 6, 'nom' => '6B.ACTIVITES ARTISTIQUE', 'points_max' => 20],
        ];

        DB::table('sous_competences')->truncate();
        foreach ($sous_competences as $sc) {
            DB::table('sous_competences')->insert([
                'competence_id' => $sc['competence_id'],
                'nom' => $sc['nom'],
                'points_max' => $sc['points_max'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}