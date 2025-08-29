<?php

namespace Database\Seeders;

use App\Models\SousCompetence;
use App\Models\Lecon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LeconsDemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Supprimer d'abord les lignes de la table couvertures (clé étrangère sur lecons)
        DB::table('couvertures')->delete();
        // Puis vider la table des leçons
        DB::table('lecons')->delete();

        // Récupérer toutes les sous-compétences
        $sousCompetences = SousCompetence::all();
        
        if ($sousCompetences->isEmpty()) {
            $this->command->info('Aucune sous-compétence trouvée dans la base de données.');
            return;
        }
        
        $count = 0;
        
        foreach ($sousCompetences as $sc) {
            // Créer 3 leçons pour chaque sous-compétence
            for ($i = 1; $i <= 3; $i++) {
                Lecon::create([
                    'sous_competence_id' => $sc->id,
                    'nom' => 'Leçon ' . $i . ' - ' . $sc->nom,
                    'total_a_couvrir_annee' => 10,
                    'total_a_couvrir_trimestre' => 5,
                    'total_a_couvrir_ua' => 2
                ]);
                $count++;
            }
        }
        
        $this->command->info("$count leçons de démonstration ont été créées avec succès !");
    }
}
