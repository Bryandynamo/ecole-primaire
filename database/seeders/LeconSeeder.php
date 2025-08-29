<?php

namespace Database\Seeders;

use App\Models\SousCompetence;
use App\Models\Lecon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LeconSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Vider la table des leçons
        DB::table('lecons')->truncate();

        // Récupérer toutes les sous-compétences
        $sousCompetences = SousCompetence::all();
        
        // Nombre de leçons à créer par sous-compétence
        $leconsParCompetence = [
            '1A.FRANCAIS' => ['Lecture', 'Écriture', 'Grammaire', 'Conjugaison', 'Orthographe', 'Vocabulaire', 'Rédaction', 'Poésie', 'Lecture suivie', 'Expression orale'],
            '1B.ANGLAIS' => ['Reading', 'Writing', 'Grammar', 'Vocabulary', 'Speaking', 'Listening', 'Phonics', 'Stories', 'Poems', 'Conversation'],
            '1C.LANGUE NATIONALE' => ['Lecture', 'Écriture', 'Grammaire', 'Conjugaison', 'Vocabulaire', 'Dictée', 'Rédaction', 'Compréhension', 'Expression orale', 'Récitation'],
            '2A.MATHEMATIQUE' => ['Nombres', 'Calcul mental', 'Opérations', 'Géométrie', 'Grandeurs et mesures', 'Résolution de problèmes', 'Logique', 'Numération', 'Fractions', 'Périmètres et aires'],
            '2C.SCIENCE ET TECHNOLOGIE' => ['Le vivant', 'Le corps humain', 'L\'environnement', 'L\'énergie', 'Les objets techniques', 'La matière', 'Le ciel et la Terre', 'Les êtres vivants', 'Les écosystèmes', 'La technologie'],
            '3A.VALEUR SOCIAL(H/G)' => ['La famille', 'L\'école', 'Le quartier', 'La ville', 'Le pays', 'Les institutions', 'Les droits et devoirs', 'L\'histoire locale', 'Les fêtes traditionnelles', 'Les symboles nationaux'],
            '3B.VALEUR CITOYENNE' => ['Le respect', 'La solidarité', 'La politesse', 'L\'honnêteté', 'Le courage', 'La persévérance', 'La tolérance', 'La responsabilité', 'Le partage', 'L\'entraide']
        ];

        foreach ($sousCompetences as $sc) {
            $nomSC = trim($sc->nom);
            
            // Vérifier si cette sous-compétence a des leçons définies
            if (isset($leconsParCompetence[$nomSC])) {
                $lecons = $leconsParCompetence[$nomSC];
                
                foreach ($lecons as $index => $nomLecon) {
                    // Créer une leçon avec des valeurs aléatoires pour les totaux
                    Lecon::create([
                        'sous_competence_id' => $sc->id,
                        'nom' => $nomLecon,
                        'total_a_couvrir_annee' => rand(5, 20),
                        'total_a_couvrir_trimestre' => rand(1, 5),
                        'total_a_couvrir_ua' => rand(1, 3)
                    ]);
                }
            } else {
                // Créer des leçons par défaut si la sous-compétence n'est pas dans la liste
                for ($i = 1; $i <= 5; $i++) {
                    Lecon::create([
                        'sous_competence_id' => $sc->id,
                        'nom' => "Leçon $i - $sc->nom",
                        'total_a_couvrir_annee' => rand(5, 20),
                        'total_a_couvrir_trimestre' => rand(1, 5),
                        'total_a_couvrir_ua' => rand(1, 3)
                    ]);
                }
            }
        }

        $this->command->info('Leçons de démonstration créées avec succès !');
    }
}
