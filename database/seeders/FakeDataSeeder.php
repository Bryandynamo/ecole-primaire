<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\Niveau;
use App\Models\Classe;
use App\Models\Session;
use App\Models\Enseignant;
use App\Models\Eleve;
use App\Models\Competence;
use App\Models\SousCompetence;
use App\Models\Modalite;
use App\Models\Note;

class FakeDataSeeder extends Seeder
{
    public function run(): void
    {
        // Sessions
        $session = Session::create([
            'nom' => '2024/2025',
            'date_debut' => '2024-09-01',
            'date_fin' => '2025-06-30',
        ]);

        // Niveaux
        $niveau = Niveau::first() ?? Niveau::create(['nom' => 'CM2']);

        // Classes
        $classe = Classe::create([
            'nom' => 'CM2A',
            'niveau_id' => $niveau->id,
            'session_id' => $session->id,
        ]);

        // Enseignants
        $enseignant = Enseignant::create([
            'nom' => 'Mbarga',
            'prenom' => 'Paul',
            'matricule' => 'ENS001',
            'classe_id' => $classe->id,
            'session_id' => $session->id,
        ]);

        // Élèves
        foreach (range(1, 5) as $i) {
            $eleves[] = Eleve::create([
                'nom' => 'Eleve'.$i,
                'prenom' => 'Test'.$i,
                'sexe' => $i % 2 == 0 ? 'F' : 'M',
                'date_naissance' => '2014-01-0'.($i+1),
                'classe_id' => $classe->id,
                'session_id' => $session->id,
            ]);
        }

        // Modalités (déjà seedées normalement)
        $modalites = Modalite::all();
        if ($modalites->count() == 0) {
            $modalites = collect([
                ['nom' => 'Orale'], ['nom' => 'Écrite'], ['nom' => 'Pratique'], ['nom' => 'Savoir-être']
            ])->map(function($m){ return Modalite::create($m); });
        }

        // Compétences et sous-compétences
        foreach ([1,2,3] as $cIdx) {
            $comp = Competence::create([
                'nom' => 'Compétence '.$cIdx,
                'description' => 'Desc '.$cIdx,
                'niveau_id' => $niveau->id,
                'points_max' => 40,
                'session_id' => $session->id,
            ]);
            foreach ([1,2] as $sIdx) {
                $sc = SousCompetence::create([
                    'competence_id' => $comp->id,
                    'nom' => 'Sous-comp '.$cIdx.'.'.$sIdx,
                    'points_max' => 20,
                ]);
                // Générer des notes pour chaque élève, modalité, trimestre
                foreach ($eleves as $eleve) {
                    foreach ($modalites as $modalite) {
                        foreach ([1,2,3] as $trimestre) {
                            Note::create([
                                'eleve_id' => $eleve->id,
                                'sous_competence_id' => $sc->id,
                                'modalite_id' => $modalite->id,
                                'session_id' => $session->id,
                                'classe_id' => $classe->id,
                                'valeur' => rand(5, 20),
                                'trimestre' => $trimestre,
                            ]);
                        }
                    }
                }
            }
        }
    }
}
