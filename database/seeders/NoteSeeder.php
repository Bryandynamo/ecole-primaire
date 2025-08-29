<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Eleve;
use App\Models\Evaluation;
use App\Models\Note;
use App\Models\SousCompetence;
use App\Models\Modalite;
use App\Models\SousCompetenceModalite;

class NoteSeeder extends Seeder
{
    public function run(): void
    {
        $evaluations = Evaluation::all();
        $sousCompetences = SousCompetence::all();

        foreach ($evaluations as $evaluation) {
            $eleves = Eleve::where('classe_id', $evaluation->classe_id)
                           ->where('session_id', $evaluation->session_id)
                           ->get();

            foreach ($eleves as $eleve) {
                foreach ($sousCompetences as $sc) {
                    // Pour chaque sous-compétence, trouver les modalités associées
                    $scms = SousCompetenceModalite::where('sous_competence_id', $sc->id)->get();

                    foreach ($scms as $scm) {
                        $pointsMax = $scm->points_max;
                        Note::firstOrCreate([
                            'eleve_id' => $eleve->id,
                            'evaluation_id' => $evaluation->id,
                            'sous_competence_id' => $sc->id,
                            'modalite_id' => $scm->modalite_id,
                            'session_id' => $evaluation->session_id,
                            'classe_id' => $evaluation->classe_id,
                            'trimestre' => $evaluation->trimestre,

                        ], [
                            'valeur' => rand((int)($pointsMax * 0.4), $pointsMax),
                        ]);
                    }
                }
            }
        }
    }
}
