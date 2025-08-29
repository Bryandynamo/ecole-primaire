<?php

namespace App\Services;

use App\Models\Classe;
use App\Models\Session;
use App\Models\Note;
use App\Models\Evaluation;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class RegistreOptimizationService
{
    /**
     * Récupère les données optimisées pour le registre
     */
    public static function getRegistreData($session_id, $classe_id, $evaluation_num)
    {
        $cacheKey = "registre_data_{$session_id}_{$classe_id}_{$evaluation_num}";
        
        return Cache::remember($cacheKey, 300, function () use ($session_id, $classe_id, $evaluation_num) {
            // Trouver l'ID réel de l'évaluation
            $evaluationModel = Evaluation::where('classe_id', $classe_id)
                ->where('session_id', $session_id)
                ->where('numero_eval', $evaluation_num)
                ->first();
                
            if (!$evaluationModel) {
                throw new \Exception('Évaluation non trouvée');
            }
            
            $evaluationId = $evaluationModel->id;
            
            // Charger toutes les données en une seule fois avec eager loading optimisé
            $classe = Classe::with([
                'eleves' => function ($query) {
                    $query->orderBy('nom');
                },
                'niveau.competences.sousCompetences.modalites' => function ($query) {
                    $query->orderBy('nom');
                }
            ])->findOrFail($classe_id);
            
            $session = Session::select('id', 'nom')->findOrFail($session_id);
            
            // Récupérer les notes avec une seule requête optimisée
            $notes = Note::select('eleve_id', 'sous_competence_id', 'modalite_id', 'valeur')
                ->where('session_id', $session_id)
                ->where('classe_id', $classe_id)
                ->where('evaluation_id', $evaluationId)
                ->get()
                ->groupBy('eleve_id')
                ->map(function($notes, $eleveId) {
                    return $notes->groupBy('sous_competence_id')
                        ->map(function($notes, $scId) {
                            return $notes->pluck('valeur', 'modalite_id')->toArray();
                        })->toArray();
                })->toArray();
            
            // Préparer les structures de données
            $ordreCompetences = [];
            $pointsMaxMap = [];
            $sousCompetenceIds = [];
            $modaliteIds = [];
            
            foreach ($classe->niveau->competences as $competence) {
                $compData = ['label' => $competence->nom, 'sous' => []];
                
                foreach ($competence->sousCompetences as $sc) {
                    $modalitesDeSC = $sc->modalites;
                    if ($modalitesDeSC->isNotEmpty()) {
                        $modalitesData = [];
                        $pointsData = [];
                        $sousCompetenceIds[$sc->nom] = $sc->id;
                        
                        foreach ($modalitesDeSC as $modalite) {
                            $modalitesData[] = $modalite->nom;
                            $modaliteIds[$modalite->nom] = $modalite->id;
                            $pointsMax = $modalite->pivot->points_max ?? 20;
                            $pointsData[] = $pointsMax;
                            $pointsMaxMap[$sc->nom][$modalite->nom] = $pointsMax;
                        }
                        
                        $compData['sous'][] = [
                            'label' => $sc->nom,
                            'modalites' => $modalitesData,
                            'points_max' => $pointsData
                        ];
                    }
                }
                
                if (!empty($compData['sous'])) {
                    $ordreCompetences[] = $compData;
                }
            }
            
            // Préparer les notes pour chaque élève
            foreach ($classe->eleves as $eleve) {
                $notesMapForEleve = [];
                if (isset($notes[$eleve->id])) {
                    foreach ($notes[$eleve->id] as $scId => $modalites) {
                        $notesMapForEleve[$scId] = $modalites;
                    }
                }
                $eleve->notes_map = $notesMapForEleve;
            }
            
            return [
                'classe' => $classe,
                'session' => $session,
                'evaluation_id' => $evaluationId,
                'evaluation_num' => $evaluation_num,
                'notes' => $notes,
                'ordreCompetences' => $ordreCompetences,
                'pointsMaxMap' => $pointsMaxMap,
                'sousCompetenceIds' => $sousCompetenceIds,
                'modaliteIds' => $modaliteIds
            ];
        });
    }
    
    /**
     * Récupère les IDs des évaluations pour un trimestre donné
     */
    private static function getEvaluationIdsForTrimestre($session_id, $classe_id, $trimestreNum)
    {
        // Récupérer toutes les évaluations de la classe/session
        $allEvaluations = Evaluation::where('classe_id', $classe_id)
            ->where('session_id', $session_id)
            ->orderBy('numero_eval')
            ->get();
            
        if ($allEvaluations->isEmpty()) {
            return [];
        }
        
        // Si il y a moins de 3 évaluations, utiliser toutes les évaluations disponibles
        if ($allEvaluations->count() <= 3) {
            return $allEvaluations->pluck('id')->toArray();
        }
        
        // Calculer les numéros d'évaluation pour ce trimestre
        $debutUA = ($trimestreNum - 1) * 3 + 1;
        $finUA = $trimestreNum * 3;
        
        // Essayer d'abord avec la logique standard
        $evaluations = $allEvaluations->whereBetween('numero_eval', [$debutUA, $finUA]);
        
        // Si aucune évaluation trouvée, utiliser une approche alternative
        if ($evaluations->isEmpty()) {
            // Prendre les évaluations par groupe de 3
            $evaluations = $allEvaluations->chunk(3)->get($trimestreNum - 1);
            if (!$evaluations) {
                // Si le trimestre demandé n'existe pas, utiliser la dernière évaluation
                $evaluations = collect([$allEvaluations->last()]);
            }
        }
        
        return $evaluations->pluck('id')->toArray();
    }
    
    /**
     * Calcule les moyennes et rangs des élèves
     */
    public static function calculateMoyennesEtRangs($eleves, $notes, $pointsMaxMap, $sousCompetenceIds, $modaliteIds)
    {
        $elevesAvecMoyennes = collect($eleves)->map(function($eleve) use ($notes, $pointsMaxMap, $sousCompetenceIds, $modaliteIds) {
            $totalPoints = 0;
            $totalPointsMax = 0;
            
            foreach($pointsMaxMap as $scLabel => $modalites) {
                $scId = $sousCompetenceIds[$scLabel] ?? null;
                if ($scId) {
                    foreach($modalites as $modaliteNom => $points) {
                        $modaliteId = $modaliteIds[$modaliteNom] ?? null;
                        if ($modaliteId && isset($notes[$eleve->id][$scId][$modaliteId])) {
                            $note = $notes[$eleve->id][$scId][$modaliteId];
                            if (is_numeric($note)) {
                                $totalPoints += $note;
                            }
                        }
                        $totalPointsMax += $points;
                    }
                }
            }
            
            $moyenne = $totalPointsMax > 0 ? ($totalPoints / $totalPointsMax) * 20 : 0;
            return [
                'eleve' => $eleve,
                'moyenne' => $moyenne,
                'total_points' => $totalPoints,
                'total_points_max' => $totalPointsMax
            ];
        })->filter();
        
        // Trier par moyenne décroissante et calculer les rangs
        return $elevesAvecMoyennes
            ->sortByDesc('moyenne')
            ->values()
            ->map(function($item, $index) {
                $item['rang'] = $index + 1;
                return $item;
            });
    }
    
    /**
     * Nettoie le cache pour une classe/session/évaluation spécifique
     */
    public static function clearCache($session_id, $classe_id, $evaluation_num = null)
    {
        if ($evaluation_num) {
            $cacheKey = "registre_data_{$session_id}_{$classe_id}_{$evaluation_num}";
            Cache::forget($cacheKey);
        } else {
            // Nettoyer tous les caches pour cette classe/session
            for ($i = 1; $i <= 9; $i++) {
                $cacheKey = "registre_data_{$session_id}_{$classe_id}_{$i}";
                Cache::forget($cacheKey);
            }
        }
    }
    
    /**
     * Valide une note par rapport aux points maximum
     */
    public static function validateNote($note, $pointsMax)
    {
        if (!is_numeric($note)) {
            return false;
        }
        
        $noteValue = (float) $note;
        return $noteValue >= 0 && $noteValue <= $pointsMax;
    }
} 