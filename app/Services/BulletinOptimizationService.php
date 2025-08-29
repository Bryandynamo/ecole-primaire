<?php

namespace App\Services;

use App\Models\Classe;
use App\Models\Session;
use App\Models\Note;
use App\Models\Evaluation;
use App\Models\Modalite;
use App\Models\SousCompetence;
use App\Models\SousCompetenceModalite;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class BulletinOptimizationService
{
    /**
     * Construit les bulletins avec des performances optimisées
     */
    public static function buildOptimizedBulletins($session_id, $classe_id, $periode = 1)
    {
        $cacheKey = "bulletins_{$session_id}_{$classe_id}_{$periode}";
        
        return Cache::remember($cacheKey, 600, function () use ($session_id, $classe_id, $periode) {
            // Utiliser le service existant mais avec cache
            return \App\Services\BulletinRegistreBuilder::build($session_id, $classe_id, $periode);
        });
    }

    /**
     * Construit les statistiques optimisées
     */
    public static function buildOptimizedStatistics($session_id, $classe_id, $periode = 1)
    {
        $cacheKey = "statistics_{$session_id}_{$classe_id}_{$periode}";
        
        return Cache::remember($cacheKey, 300, function () use ($session_id, $classe_id, $periode) {
            try {
                \Log::info('Début du calcul des statistiques optimisées', [
                    'session_id' => $session_id,
                    'classe_id' => $classe_id,
                    'periode' => $periode
                ]);

                // Détecter si c'est un trimestre ou une UA
                if (is_string($periode) && preg_match('/trimestre(\d)/i', $periode, $matches)) {
                    $trimestreNum = (int)$matches[1];
                    \Log::info('Calcul des statistiques pour le trimestre', ['trimestre' => $trimestreNum]);
                    return self::calculateTrimestreStatistics($session_id, $classe_id, $trimestreNum);
                } else {
                    // C'est une UA simple
                    \Log::info('Calcul des statistiques pour une UA', ['ua' => $periode]);
                    $registreData = RegistreOptimizationService::getRegistreData($session_id, $classe_id, $periode);
                    return self::calculateSingleEvaluationStatistics($registreData);
                }

            } catch (\Exception $e) {
                \Log::error('Erreur lors du calcul des statistiques optimisées', [
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'session_id' => $session_id,
                    'classe_id' => $classe_id,
                    'periode' => $periode
                ]);
                throw $e;
            }
        });
    }
    
    /**
     * Calcule les statistiques pour une évaluation simple
     */
    private static function calculateSingleEvaluationStatistics($registreData)
    {
        $classe = $registreData['classe'];
        $notes = $registreData['notes'];
        $pointsMaxMap = $registreData['pointsMaxMap'];
        $sousCompetenceIds = $registreData['sousCompetenceIds'];
        $modaliteIds = $registreData['modaliteIds'];

        $eleves = $classe->eleves;
        $inscrits = $eleves->count();
        $inscrits_garcons = $eleves->where('sexe', 'M')->count();
        $inscrits_filles = $eleves->where('sexe', 'F')->count();

        $moyennes = [];
        $moyennes_garcons = [];
        $moyennes_filles = [];
        $ayant_compose = 0;
        $ayant_compose_garcons = 0;
        $ayant_compose_filles = 0;
        $admis = 0;
        $admis_garcons = 0;
        $admis_filles = 0;
        $echoues = 0;
        $echoues_garcons = 0;
        $echoues_filles = 0;

        foreach ($eleves as $eleve) {
            $total = 0;
            $totalMax = 0;
            $hasNote = false;

            foreach ($pointsMaxMap as $scLabel => $modalites) {
                $scId = $sousCompetenceIds[$scLabel] ?? null;
                if ($scId && isset($notes[$eleve->id][$scId])) {
                    foreach ($modalites as $modaliteNom => $points) {
                        $modaliteId = $modaliteIds[$modaliteNom] ?? null;
                        if ($modaliteId && isset($notes[$eleve->id][$scId][$modaliteId])) {
                            $valeur = $notes[$eleve->id][$scId][$modaliteId];
                            if (is_numeric($valeur)) {
                                $hasNote = true;
                                $total += (float)$valeur;
                            }
                        }
                        $totalMax += (float)$points;
                    }
                }
            }

            $moyenne = $totalMax > 0 ? round(($total / $totalMax) * 20, 2) : 0;

            if ($hasNote && $moyenne > 0) {
                $moyennes[] = $moyenne;
                if ($eleve->sexe === 'M') {
                    $moyennes_garcons[] = $moyenne;
                    $ayant_compose_garcons++;
                    if ($moyenne >= 10) $admis_garcons++;
                    else $echoues_garcons++;
                }
                if ($eleve->sexe === 'F') {
                    $moyennes_filles[] = $moyenne;
                    $ayant_compose_filles++;
                    if ($moyenne >= 10) $admis_filles++;
                    else $echoues_filles++;
                }
                $ayant_compose++;
                if ($moyenne >= 10) $admis++;
                else $echoues++;
            }
        }

        return self::buildStatisticsResult($inscrits, $inscrits_garcons, $inscrits_filles, $ayant_compose, 
            $ayant_compose_garcons, $ayant_compose_filles, $admis, $admis_garcons, $admis_filles, 
            $echoues, $echoues_garcons, $echoues_filles, $moyennes, $moyennes_garcons, $moyennes_filles);
    }
    
    /**
     * Calcule les statistiques pour un trimestre
     */
    private static function calculateTrimestreStatistics($session_id, $classe_id, $trimestreNum)
    {
        // Calculer les numéros d'évaluation pour ce trimestre
        $debutUA = ($trimestreNum - 1) * 3 + 1;
        $finUA = $trimestreNum * 3;
        
        \Log::info('Recherche des évaluations pour le trimestre', [
            'trimestre' => $trimestreNum,
            'debut_ua' => $debutUA,
            'fin_ua' => $finUA,
            'session_id' => $session_id,
            'classe_id' => $classe_id
        ]);
        
        // Récupérer toutes les évaluations du trimestre
        $evaluations = Evaluation::where('classe_id', $classe_id)
            ->where('session_id', $session_id)
            ->whereBetween('numero_eval', [$debutUA, $finUA])
            ->orderBy('numero_eval')
            ->get();
            
        \Log::info('Évaluations trouvées pour le trimestre', [
            'trimestre' => $trimestreNum,
            'count' => $evaluations->count(),
            'evaluations' => $evaluations->pluck('numero_eval')->toArray()
        ]);
            
        if ($evaluations->isEmpty()) {
            // Essayer une approche alternative si aucune évaluation trouvée
            $allEvaluations = Evaluation::where('classe_id', $classe_id)
                ->where('session_id', $session_id)
                ->orderBy('numero_eval')
                ->get();
                
            \Log::info('Toutes les évaluations disponibles', [
                'count' => $allEvaluations->count(),
                'evaluations' => $allEvaluations->pluck('numero_eval')->toArray()
            ]);
            
            // Prendre les évaluations par groupe de 3
            $evaluations = $allEvaluations->chunk(3)->get($trimestreNum - 1);
            if (!$evaluations) {
                // Si le trimestre demandé n'existe pas, utiliser la dernière évaluation
                $evaluations = collect([$allEvaluations->last()]);
            }
            
            \Log::info('Évaluations sélectionnées après fallback', [
                'count' => $evaluations->count(),
                'evaluations' => $evaluations->pluck('numero_eval')->toArray()
            ]);
        }
        
        if ($evaluations->isEmpty()) {
            throw new \Exception("Aucune évaluation trouvée pour le trimestre {$trimestreNum}");
        }
        
        // Récupérer les données de base
        $classe = Classe::with([
            'eleves' => function ($query) {
                $query->orderBy('nom');
            },
            'niveau.competences.sousCompetences.modalites'
        ])->findOrFail($classe_id);
        
        // Récupérer toutes les notes du trimestre
        $evaluationIds = $evaluations->pluck('id')->toArray();
        $notes = Note::where('session_id', $session_id)
            ->where('classe_id', $classe_id)
            ->whereIn('evaluation_id', $evaluationIds)
            ->get();
            
        // Construire la structure des compétences
        $pointsMaxMap = [];
        $sousCompetenceIds = [];
        $modaliteIds = [];
        
        foreach ($classe->niveau->competences as $competence) {
            foreach ($competence->sousCompetences as $sc) {
                $sousCompetenceIds[$sc->nom] = $sc->id;
                foreach ($sc->modalites as $modalite) {
                    $modaliteIds[$modalite->nom] = $modalite->id;
                    $pointsMax = $modalite->pivot->points_max ?? 20;
                    $pointsMaxMap[$sc->nom][$modalite->nom] = $pointsMax;
                }
            }
        }
        
        // Calculer les moyennes pour chaque élève
        $eleves = $classe->eleves;
        $inscrits = $eleves->count();
        $inscrits_garcons = $eleves->where('sexe', 'M')->count();
        $inscrits_filles = $eleves->where('sexe', 'F')->count();

        $moyennes = [];
        $moyennes_garcons = [];
        $moyennes_filles = [];
        $ayant_compose = 0;
        $ayant_compose_garcons = 0;
        $ayant_compose_filles = 0;
        $admis = 0;
        $admis_garcons = 0;
        $admis_filles = 0;
        $echoues = 0;
        $echoues_garcons = 0;
        $echoues_filles = 0;

        foreach ($eleves as $eleve) {
            $totalTrimestre = 0;
            $totalMaxTrimestre = 0;
            $hasNote = false;
            $evaluationsAvecNotes = 0;

            // Calculer la moyenne sur toutes les évaluations du trimestre
            foreach ($evaluations as $evaluation) {
                $totalEval = 0;
                $totalMaxEval = 0;
                $hasNoteEval = false;

                foreach ($pointsMaxMap as $scLabel => $modalites) {
                    $scId = $sousCompetenceIds[$scLabel] ?? null;
                    if ($scId) {
                        foreach ($modalites as $modaliteNom => $points) {
                            $modaliteId = $modaliteIds[$modaliteNom] ?? null;
                            if ($modaliteId) {
                                $note = $notes->where('eleve_id', $eleve->id)
                                    ->where('evaluation_id', $evaluation->id)
                                    ->where('sous_competence_id', $scId)
                                    ->where('modalite_id', $modaliteId)
                                    ->first();
                                    
                                if ($note && is_numeric($note->valeur)) {
                                    $hasNoteEval = true;
                                    $totalEval += (float)$note->valeur;
                                }
                            }
                            $totalMaxEval += (float)$points;
                        }
                    }
                }

                if ($hasNoteEval && $totalMaxEval > 0) {
                    $totalTrimestre += $totalEval;
                    $totalMaxTrimestre += $totalMaxEval;
                    $evaluationsAvecNotes++;
                    $hasNote = true;
                }
            }

            if ($hasNote && $totalMaxTrimestre > 0) {
                $moyenne = round(($totalTrimestre / $totalMaxTrimestre) * 20, 2);
                $moyennes[] = $moyenne;
                
                if ($eleve->sexe === 'M') {
                    $moyennes_garcons[] = $moyenne;
                    $ayant_compose_garcons++;
                    if ($moyenne >= 10) $admis_garcons++;
                    else $echoues_garcons++;
                }
                if ($eleve->sexe === 'F') {
                    $moyennes_filles[] = $moyenne;
                    $ayant_compose_filles++;
                    if ($moyenne >= 10) $admis_filles++;
                    else $echoues_filles++;
                }
                $ayant_compose++;
                if ($moyenne >= 10) $admis++;
                else $echoues++;
            }
        }

        return self::buildStatisticsResult($inscrits, $inscrits_garcons, $inscrits_filles, $ayant_compose, 
            $ayant_compose_garcons, $ayant_compose_filles, $admis, $admis_garcons, $admis_filles, 
            $echoues, $echoues_garcons, $echoues_filles, $moyennes, $moyennes_garcons, $moyennes_filles);
    }
    
    /**
     * Construit le tableau de résultats des statistiques
     */
    private static function buildStatisticsResult($inscrits, $inscrits_garcons, $inscrits_filles, $ayant_compose, 
        $ayant_compose_garcons, $ayant_compose_filles, $admis, $admis_garcons, $admis_filles, 
        $echoues, $echoues_garcons, $echoues_filles, $moyennes, $moyennes_garcons, $moyennes_filles)
    {
        $moyenne_generale = count($moyennes) > 0 ? round(array_sum($moyennes) / count($moyennes), 2) : 0;
        $moyenne_generale_garcons = count($moyennes_garcons) > 0 ? round(array_sum($moyennes_garcons) / count($moyennes_garcons), 2) : 0;
        $moyenne_generale_filles = count($moyennes_filles) > 0 ? round(array_sum($moyennes_filles) / count($moyennes_filles), 2) : 0;
        
        $pourc_reussite = $ayant_compose > 0 ? round($admis * 100 / $ayant_compose, 2) : 0;
        $pourc_echec = $ayant_compose > 0 ? round($echoues * 100 / $ayant_compose, 2) : 0;
        $pourc_reussite_garcons = $ayant_compose_garcons > 0 ? round($admis_garcons * 100 / $ayant_compose_garcons, 2) : 0;
        $pourc_echec_garcons = $ayant_compose_garcons > 0 ? round($echoues_garcons * 100 / $ayant_compose_garcons, 2) : 0;
        $pourc_reussite_filles = $ayant_compose_filles > 0 ? round($admis_filles * 100 / $ayant_compose_filles, 2) : 0;
        $pourc_echec_filles = $ayant_compose_filles > 0 ? round($echoues_filles * 100 / $ayant_compose_filles, 2) : 0;

        return [
            'inscrits' => $inscrits,
            'inscrits_garcons' => $inscrits_garcons,
            'inscrits_filles' => $inscrits_filles,
            'ayant_compose' => $ayant_compose,
            'ayant_compose_garcons' => $ayant_compose_garcons,
            'ayant_compose_filles' => $ayant_compose_filles,
            'admis' => $admis,
            'admis_garcons' => $admis_garcons,
            'admis_filles' => $admis_filles,
            'echoues' => $echoues,
            'echoues_garcons' => $echoues_garcons,
            'echoues_filles' => $echoues_filles,
            'moyenne_generale' => $moyenne_generale,
            'moyenne_generale_garcons' => $moyenne_generale_garcons,
            'moyenne_generale_filles' => $moyenne_generale_filles,
            'pourc_reussite' => $pourc_reussite,
            'pourc_echec' => $pourc_echec,
            'pourc_reussite_garcons' => $pourc_reussite_garcons,
            'pourc_echec_garcons' => $pourc_echec_garcons,
            'pourc_reussite_filles' => $pourc_reussite_filles,
            'pourc_echec_filles' => $pourc_echec_filles,
            'moyenne_premier' => count($moyennes) > 0 ? round(max($moyennes), 2) : '-',
            'moyenne_dernier' => count($moyennes) > 0 ? round(min($moyennes), 2) : '-',
            'moyenne_premier_garcons' => count($moyennes_garcons) > 0 ? round(max($moyennes_garcons), 2) : '-',
            'moyenne_dernier_garcons' => count($moyennes_garcons) > 0 ? round(min($moyennes_garcons), 2) : '-',
            'moyenne_premier_filles' => count($moyennes_filles) > 0 ? round(max($moyennes_filles), 2) : '-',
            'moyenne_dernier_filles' => count($moyennes_filles) > 0 ? round(min($moyennes_filles), 2) : '-',
        ];
    }

    /**
     * Détermine les numéros d'UA pour une période donnée
     */
    private static function getUANumbers($session_id, $classe_id, $periode)
    {
        if (is_string($periode) && preg_match('/trimestre(\d)/i', $periode, $m)) {
            $trim = (int)$m[1];
            $start = ($trim - 1) * 3 + 1;
            return [$start, $start + 1, $start + 2];
        } elseif (is_numeric($periode)) {
            return [(int)$periode];
        } else {
            return Evaluation::where('session_id', $session_id)
                ->where('classe_id', $classe_id)
                ->orderBy('numero_eval')
                ->pluck('numero_eval')
                ->unique()
                ->values()
                ->toArray();
        }
    }

    /**
     * Construit la structure des compétences
     */
    private static function buildStructure($classe, $scmPoints)
    {
        $structure = [];
        foreach ($classe->niveau->competences as $comp) {
            $scs = [];
            foreach ($comp->sousCompetences as $sc) {
                $mods = [];
                foreach ($sc->modalites as $mod) {
                    $key = $sc->id . '-' . $mod->id;
                    $pts = isset($scmPoints[$key]) ? ($scmPoints[$key]->points_max ?? $scmPoints[$key]->points ?? '') : ($mod->points ?? '');
                    $mods[] = [
                        'mod' => $mod->nom . ' ' . $pts,
                        'pts' => $pts,
                        'scid' => $sc->id,
                        'modid' => $mod->id,
                    ];
                }
                $scs[] = [
                    'sc' => $sc->nom,
                    'mods' => $mods
                ];
            }
            $structure[] = [
                'comp' => $comp->nom,
                'scs' => $scs
            ];
        }
        return $structure;
    }

    /**
     * Construit la map des notes pour un élève
     */
    private static function buildNotesMap($eleveNotes, $uaNums)
    {
        $notesMap = [];
        foreach ($eleveNotes as $note) {
            $eval = Evaluation::find($note->evaluation_id);
            $numero_eval = $eval ? (string)$eval->numero_eval : (string)$note->evaluation_id;
            
            if (in_array($numero_eval, array_map('strval', $uaNums))) {
                $scid = (string)$note->sous_competence_id;
                $modid = (string)$note->modalite_id;
                $notesMap[$scid][$modid][$numero_eval] = $note->valeur;
            }
        }

        // Compléter avec les UA manquantes
        foreach ($notesMap as $scid => &$modalites) {
            foreach ($modalites as $modid => &$uas) {
                foreach ($uaNums as $ua) {
                    $uaStr = (string)$ua;
                    if (!array_key_exists($uaStr, $uas)) {
                        $uas[$uaStr] = '';
                    }
                }
                ksort($uas);
            }
        }
        unset($uas, $modalites);

        return $notesMap;
    }

    /**
     * Calcule les moyennes et totaux
     */
    private static function calculateAverages($notesMap, $structure, $uaNums)
    {
        // Calcul totaux par sous-compétence
        $totauxSousComp = [];
        foreach ($notesMap as $scid => $modalites) {
            foreach ($modalites as $modid => $evals) {
                $tot = 0;
                foreach ($evals as $ua => $val) {
                    $tot += is_numeric($val) ? (float)$val : 0;
                }
                $totauxSousComp[$scid][$modid] = $tot;
            }
        }

        // Calcul moyennes par UA
        $uaMoyennes = [];
        foreach ($uaNums as $ua) {
            $totalUA = 0;
            $totalMaxUA = 0;
            foreach ($structure as $block) {
                foreach ($block['scs'] as $sc) {
                    foreach ($sc['mods'] as $mod) {
                        $scid = $mod['scid'];
                        $modid = $mod['modid'];
                        $val = $notesMap[$scid][$modid][$ua] ?? '';
                        $totalUA += is_numeric($val) ? (float)$val : 0;
                        $totalMaxUA += is_numeric($mod['pts']) ? (float)$mod['pts'] : 0;
                    }
                }
            }
            $uaMoyennes[$ua] = $totalMaxUA > 0 ? round(($totalUA / $totalMaxUA) * 20, 2) : 0;
        }

        $moyenne = count($uaMoyennes) > 0 ? round(array_sum($uaMoyennes) / count($uaMoyennes), 2) : 0;

        return [
            'totauxSousComp' => $totauxSousComp,
            'uaMoyennes' => $uaMoyennes,
            'moyenne' => $moyenne
        ];
    }

    /**
     * Calcule les rangs pour tous les bulletins
     */
    private static function calculateRanks($bulletins, $uaNums)
    {
        // Rangs par UA
        $rangsParUA = [];
        foreach ($uaNums as $ua) {
            $moyennesUA = [];
            foreach ($bulletins as $i => $bulletin) {
                $moyennesUA[] = [
                    'index' => $i,
                    'moyenne' => $bulletin['totalGeneral'][$ua] ?? 0
                ];
            }
            usort($moyennesUA, function($a, $b) {
                return $b['moyenne'] <=> $a['moyenne'];
            });
            
            $rangsParUA[$ua] = [];
            $currentRank = 1;
            $lastMoyenne = null;
            foreach ($moyennesUA as $i => $item) {
                if ($lastMoyenne !== null && $item['moyenne'] < $lastMoyenne) {
                    $currentRank = $i + 1;
                }
                $rangsParUA[$ua][$item['index']] = $currentRank;
                $lastMoyenne = $item['moyenne'];
            }
        }

        // Rangs par trimestre
        $moyennesTrimestre = [];
        foreach ($bulletins as $i => $bulletin) {
            $moyennesTrimestre[] = [
                'index' => $i,
                'moyenne' => $bulletin['moyenne'] ?? 0
            ];
        }
        usort($moyennesTrimestre, function($a, $b) {
            return $b['moyenne'] <=> $a['moyenne'];
        });
        
        $rangsParTrimestre = [];
        $currentRank = 1;
        $lastMoyenne = null;
        foreach ($moyennesTrimestre as $i => $item) {
            if ($lastMoyenne !== null && $item['moyenne'] < $lastMoyenne) {
                $currentRank = $i + 1;
            }
            $rangsParTrimestre[$item['index']] = $currentRank;
            $lastMoyenne = $item['moyenne'];
        }

        // Ajouter les rangs aux bulletins
        foreach ($bulletins as $i => &$bulletin) {
            $bulletin['rangsUA'] = [];
            foreach ($uaNums as $ua) {
                $bulletin['rangsUA'][$ua] = $rangsParUA[$ua][$i] ?? 0;
            }
            $bulletin['rangTrimestre'] = $rangsParTrimestre[$i] ?? 0;
        }
        unset($bulletin);

        // Tri par mérite et ajout du rang final
        usort($bulletins, function($a, $b) {
            return ($b['moyenne'] ?? 0) <=> ($a['moyenne'] ?? 0);
        });

        // Profil de classe
        $moyennes = array_column($bulletins, 'moyenne');
        $nbMoyenne10 = count(array_filter($moyennes, function($m) { return $m >= 10; }));
        $moyenneClasse = count($moyennes) ? round(array_sum($moyennes)/count($moyennes),2) : 0;
        $moyennePremier = count($moyennes) ? max($moyennes) : 0;
        $moyenneDernier = count($moyennes) ? min($moyennes) : 0;
        $tauxReussite = count($moyennes) ? round($nbMoyenne10*100/count($moyennes),2) : 0;

        foreach ($bulletins as $i => &$b) {
            $b['rang'] = $i+1;
            $b['profilClasse'] = [
                'moyenne_10' => $nbMoyenne10,
                'moyenne_classe' => $moyenneClasse,
                'taux_reussite' => $tauxReussite,
                'moyenne_premier' => $moyennePremier,
                'moyenne_dernier' => $moyenneDernier,
            ];
        }
        unset($b);

        return $bulletins;
    }

    /**
     * Nettoie le cache pour une classe/session/évaluation spécifique
     */
    public static function clearCache($session_id, $classe_id, $periode = null)
    {
        if ($periode) {
            $cacheKey = "bulletins_{$session_id}_{$classe_id}_{$periode}";
            Cache::forget($cacheKey);
            
            $cacheKey = "statistics_{$session_id}_{$classe_id}_{$periode}";
            Cache::forget($cacheKey);
        } else {
            // Nettoyer tous les caches pour cette classe/session
            for ($i = 1; $i <= 9; $i++) {
                $cacheKey = "bulletins_{$session_id}_{$classe_id}_{$i}";
                Cache::forget($cacheKey);
                
                $cacheKey = "statistics_{$session_id}_{$classe_id}_{$i}";
                Cache::forget($cacheKey);
            }
        }
    }
} 