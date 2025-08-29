<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Lecon;
use App\Models\Evaluation;

class LeconEvaluationSeeder extends Seeder
{
    public function run(): void
    {
        // Pour chaque leçon et chaque évaluation, crée une ligne pivot si elle n'existe pas
        $lecons = Lecon::all();
        $evaluations = Evaluation::all();
        foreach ($lecons as $lecon) {
            foreach ($evaluations as $eval) {
                // Détermine le "prévu" pour cette leçon/UA
                // Si tu as une logique spéciale, adapte ici
                $prevu = $lecon->total_a_couvrir_trimestre ?? 1; // Valeur par défaut raisonnable
                // Insère seulement si absent
                DB::table('lecon_evaluation')->updateOrInsert([
                    'lecon_id' => $lecon->id,
                    'evaluation_id' => $eval->id,
                ], [
                    'total_a_couvrir_ua' => $prevu,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
