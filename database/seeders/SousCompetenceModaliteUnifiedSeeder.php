<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\SousCompetence;
use App\Models\Modalite;
use App\Models\SousCompetenceModalite;
use Carbon\Carbon;

class SousCompetenceModaliteUnifiedSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $now = Carbon::now();
        $defaultPointsMax = 20;

        // Structure d'ordre fixe pour les compétences (conforme à vos données)
        $ordreCompetences = [
            [
                'label' => 'COMPETENCE1',
                'sous' => [
                    ['label' => '1.A.FRANCAIS', 'modalites' => ['Orale','Ecrite','Savoir-être','Total'], 'points_max' => [20,15,5,40]],
                    ['label' => '1B.ENGLISH', 'modalites' => ['Orale','Written','Attitude','Total'], 'points_max' => [20,15,5,40]],
                    ['label' => '1C.LANGUE NATIONALITE', 'modalites' => ['Orale','Ecrite','Savoir-être','Total'], 'points_max' => [20,15,5,40]],
                ]
            ],
            [
                'label' => 'COMPETENCE2',
                'sous' => [
                    ['label' => '2A.MATHEMATIQUE', 'modalites' => ['Orale','Ecrite','Pratique','Savoir-être','Total'], 'points_max' => [5,5,5,5,20]],
                    ['label' => '2B.SCIENCES et TECHNOLOGIES', 'modalites' => ['Orale','Ecrite','Savoir-être','Total'], 'points_max' => [5,20,5,30]],
                ]
            ],
            [
                'label' => 'COMPETENCE3',
                'sous' => [
                    ['label' => '3A.VALEUR SOCIALE(H/G)', 'modalites' => ['Orale','Ecrite','Savoir-être','Total'], 'points_max' => [5,5,15,5,30]],
                    ['label' => '3B. VALEURS CITOYENNES', 'modalites' => ['Orale','Ecrite','Pratique','Savoir-être','Total'], 'points_max' => [3,3,10,4,20]],
                ]
            ],
            [
                'label' => 'COMPETENCE4',
                'sous' => [
                    ['label' => '4 DeVELOPEMENT PERSONNEL', 'modalites' => ['Orale','Ecrite','Pratique','Savoir-être','Total'], 'points_max' => [3,3,10,4,20]],
                ]
            ],
            [
                'label' => 'COMPETENCE5',
                'sous' => [
                    ['label' => '5.TIC', 'modalites' => ['Orale','Ecrite','Pratique','Savoir-être','Total'], 'points_max' => [5,3,10,2,20]],
                ]
            ],
            [
                'label' => 'COMPETENCE6',
                'sous' => [
                    ['label' => '6A. EPS', 'modalites' => ['Orale','Ecrite','Pratique','Savoir-être','Total'], 'points_max' => [3,3,10,4,20]],
                    ['label' => '6B. ACTIVITES ARTISTIQUE', 'modalites' => ['Orale','Ecrite','Pratique','Savoir-être','Total'], 'points_max' => [3,3,10,4,20]],
                ]
            ],
        ];

        // Créer les mappings des IDs
        $sousCompetenceIds = [];
        $modaliteIds = [];

        // Remplir les mappings avec les données existantes
        foreach ($ordreCompetences as $comp) {
            foreach ($comp['sous'] as $sc) {
                $sousCompetence = SousCompetence::where('nom', $sc['label'])->first();
                if ($sousCompetence) {
                    $sousCompetenceIds[$sc['label']] = $sousCompetence->id;
                    foreach ($sc['modalites'] as $modaliteNom) {
                        $modalite = Modalite::where('nom', $modaliteNom)->first();
                        if ($modalite) {
                            $modaliteIds[$modaliteNom] = $modalite->id;
                        }
                    }
                }
            }
        }

        // Étape 1: Créer toutes les relations possibles avec points_max par défaut
        $sousCompetences = SousCompetence::all();
        $modalites = Modalite::all();
        
        foreach ($sousCompetences as $sc) {
            foreach ($modalites as $mod) {
                DB::table('sous_competence_modalites')->updateOrInsert([
                    'sous_competence_id' => $sc->id,
                    'modalite_id' => $mod->id
                ], [
                    'points_max' => $defaultPointsMax,
                    'updated_at' => $now,
                    'created_at' => $now,
                ]);
            }
        }

        // Étape 2: Appliquer les points_max spécifiques selon votre structure
        foreach ($ordreCompetences as $comp) {
            foreach ($comp['sous'] as $sc) {
                $scId = $sousCompetenceIds[$sc['label']] ?? null;
                if ($scId) {
                    foreach ($sc['modalites'] as $mIdx => $modaliteNom) {
                        $modaliteId = $modaliteIds[$modaliteNom] ?? null;
                        if ($modaliteId) {
                            DB::table('sous_competence_modalites')->updateOrInsert([
                                'sous_competence_id' => $scId,
                                'modalite_id' => $modaliteId
                            ], [
                                'points_max' => $sc['points_max'][$mIdx] ?? $defaultPointsMax,
                                'updated_at' => $now,
                                'created_at' => $now,
                            ]);
                        }
                    }
                }
            }
        }

        // Étape 3: S'assurer qu'aucune relation n'a de points_max null ou <= 0
        DB::table('sous_competence_modalites')
            ->whereNull('points_max')
            ->orWhere('points_max', '<=', 0)
            ->update(['points_max' => $defaultPointsMax]);

        $this->command->info('Seeder unifié sous_competence_modalites terminé avec succès!');
    }
} 