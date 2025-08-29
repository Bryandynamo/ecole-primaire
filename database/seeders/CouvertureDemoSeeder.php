<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Classe;
use App\Models\Evaluation;
use App\Models\SousCompetence;
use App\Models\Lecon;
use App\Models\Couverture;

class CouvertureDemoSeeder extends Seeder
{
    public function run()
    {
        // Exemple : Générer une classe, des sous-compétences, des leçons, des évaluations et des couvertures
        $classe = Classe::firstOrCreate(['nom' => 'CE1', 'niveau_id' => 1, 'session_id' => 1]);

        $sousCompetence = SousCompetence::firstOrCreate([
            'competence_id' => 1,
            'nom' => 'Grammaire',
            'points_max' => 20,
        ]);

        // Trois leçons pour la sous-compétence
        $lecons = [
            Lecon::firstOrCreate([
                'sous_competence_id' => $sousCompetence->id,
                'nom' => 'Phrase simple',
                'total_a_couvrir_trimestre' => 10,
                'total_a_couvrir_annee' => 30,
            ]),
            Lecon::firstOrCreate([
                'sous_competence_id' => $sousCompetence->id,
                'nom' => 'Phrase complexe',
                'total_a_couvrir_trimestre' => 8,
                'total_a_couvrir_annee' => 24,
            ]),
            Lecon::firstOrCreate([
                'sous_competence_id' => $sousCompetence->id,
                'nom' => 'Types de phrases',
                'total_a_couvrir_trimestre' => 7,
                'total_a_couvrir_annee' => 21,
            ]),
        ];

        // Récupère le premier enseignant existant
        $enseignant = \App\Models\Enseignant::first();
        if (!$enseignant) {
            throw new \Exception('Aucun enseignant trouvé. Veuillez créer au moins un enseignant avant de lancer ce seeder.');
        }
        // Deux évaluations (UA) pour le trimestre
        $eval1 = Evaluation::firstOrCreate([
            'classe_id' => $classe->id,
            'trimestre' => 1,
            'numero_eval' => 1,
            'date_eval' => '2025-01-15',
            'session_id' => 1,
            'enseignant_id' => $enseignant->id,
        ]);
        $eval2 = Evaluation::firstOrCreate([
            'classe_id' => $classe->id,
            'trimestre' => 1,
            'numero_eval' => 2,
            'date_eval' => '2025-02-15',
            'session_id' => 1,
            'enseignant_id' => $enseignant->id,
        ]);

        // Couvertures pour chaque leçon et chaque UA
        foreach ($lecons as $lecon) {
            Couverture::updateOrCreate([
                'classe_id' => $classe->id,
                'evaluation_id' => $eval1->id,
                'lecon_id' => $lecon->id,
                'sous_competence_id' => $sousCompetence->id,
            ], [
                'nb_couverts' => rand(2, 6)
            ]);
            Couverture::updateOrCreate([
                'classe_id' => $classe->id,
                'evaluation_id' => $eval2->id,
                'lecon_id' => $lecon->id,
                'sous_competence_id' => $sousCompetence->id,
            ], [
                'nb_couverts' => rand(3, 7)
            ]);
        }
    }
}
