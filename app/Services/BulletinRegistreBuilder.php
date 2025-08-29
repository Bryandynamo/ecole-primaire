<?php
namespace App\Services;

use App\Models\Classe;
use App\Models\Session;
use App\Models\Note;
use App\Models\Modalite;
use App\Models\SousCompetence;
use App\Models\SousCompetenceModalite;

class BulletinRegistreBuilder
{
    public static function build($session_id, $classe_id, $periode = 1)
    {
        // Découpage des trimestres : T1=UA1-3, T2=UA4-6, T3=UA7-9
        $uaNums = [];
        if (is_string($periode) && preg_match('/trimestre(\d)/i', $periode, $m)) {
            $trim = (int)$m[1];
            $start = ($trim - 1) * 3 + 1;
            $uaNums = [$start, $start+1, $start+2];
        } elseif (is_numeric($periode)) {
            $uaNums = [(int)$periode];
        } else {
            // Par défaut, tous les UA existants
            $uaNums = \App\Models\Evaluation::where('session_id', $session_id)
                ->where('classe_id', $classe_id)
                ->orderBy('numero_eval')
                ->pluck('numero_eval')
                ->unique()
                ->values()
                ->toArray();
            if (empty($uaNums)) {
                $uaNums = [1];
            }
        }
        $evalIds = \App\Models\Evaluation::where('session_id', $session_id)
            ->where('classe_id', $classe_id)
            ->whereIn('numero_eval', $uaNums)
            ->pluck('id')
            ->toArray();
        $classe = Classe::with(['eleves', 'niveau.competences.sousCompetences.modalites'])->findOrFail($classe_id);
        $session = Session::findOrFail($session_id);
        $eleves = $classe->eleves;
        $notes = Note::where('session_id', $session_id)
            ->where('classe_id', $classe_id)
            ->whereIn('evaluation_id', $evalIds)
            ->get();
        $modalites = Modalite::all()->keyBy('id');
        $sousCompetences = SousCompetence::all()->keyBy('id');
        $scmPoints = SousCompetenceModalite::all()->keyBy(function($item) {
            return $item->sous_competence_id . '-' . $item->modalite_id;
        });

        // Structure officielle du modèle
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

        // Préparer les bulletins par élève
        $bulletins = [];
        foreach ($eleves as $eleve) {
            $notesMap = [];
            foreach ($notes as $note) {
                if ($note->eleve_id == $eleve->id) {
                    $scid = (string)$note->sous_competence_id;
                    $modid = (string)$note->modalite_id;
                    $eval = \App\Models\Evaluation::find($note->evaluation_id);
                    $numero_eval = $eval ? (string)$eval->numero_eval : (string)$note->evaluation_id;
                    
                    // Ne garder que les notes des UA demandées
                    if (in_array($numero_eval, array_map('strval', $uaNums))) {
                        $notesMap[$scid][$modid][$numero_eval] = $note->valeur;
                    }
                }
            }
            // Correction ALIGNEMENT : on force la présence des UA demandées pour chaque (scid, modid)
            foreach ($notesMap as $scid => &$modalites) {
                foreach ($modalites as $modid => &$uas) {
                    foreach ($uaNums as $ua) {
                        $uaStr = (string)$ua;
                        if (!array_key_exists($uaStr, $uas)) {
                            $uas[$uaStr] = '';
                        }
                    }
                    ksort($uas); // Toujours l'ordre numérique
                }
            }
            unset($uas); unset($modalites);
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
            // Calcul total général et moyenne trimestrielle CORRECTE
            $evals = \App\Models\Evaluation::whereIn('id', $evalIds)->orderBy('numero_eval')->get();
            $uaMoyennes = [];
            foreach ($evals as $eval) {
                $ua = $eval->numero_eval;
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
                if ($totalMaxUA > 0) {
                    $uaMoyennes[$ua] = round(($totalUA / $totalMaxUA) * 20, 2);
                } else {
                    $uaMoyennes[$ua] = 0;
                }
            }
            // Déterminer la moyenne affichée selon le contexte demandé
            if (is_string($periode) && preg_match('/trimestre(\d)/i', $periode)) {
                // Trimestre: prendre uniquement la dernière UA du trimestre (ex: UA 3, 6, 9)
                $lastUA = !empty($uaNums) ? max($uaNums) : null;
                $moyenne = $lastUA !== null ? ($uaMoyennes[$lastUA] ?? 0) : 0;
            } elseif (is_null($periode)) {
                // Annuel: moyenne basée uniquement sur les dernières UAs de chaque trimestre (3, 6, 9)
                $uaKeys = array_keys($uaMoyennes);
                $uasDernieres = array_values(array_intersect([3, 6, 9], $uaKeys));
                if (count($uasDernieres) > 0) {
                    $sum = 0;
                    foreach ($uasDernieres as $uaSel) { $sum += $uaMoyennes[$uaSel] ?? 0; }
                    $moyenne = round($sum / count($uasDernieres), 2);
                } else {
                    // Si aucune des UA 3,6,9 n'existe, fallback sur la moyenne classique
                    $moyenne = count($uaMoyennes) > 0 ? round(array_sum($uaMoyennes) / count($uaMoyennes), 2) : 0;
                }
            } else {
                // UA simple (ou autre): conserver le comportement existant
                $moyenne = count($uaMoyennes) > 0 ? round(array_sum($uaMoyennes) / count($uaMoyennes), 2) : 0;
            }
            
            // Nettoyage de la structure pour éviter les incohérences d'affichage
            $cleanStructure = [];
            foreach ($structure as $block) {
                $cleanScs = [];
                foreach ($block['scs'] as $sc) {
                    $cleanMods = [];
                    foreach ($sc['mods'] as $mod) {
                        if (!empty($mod['mod']) && !empty($mod['scid']) && !empty($mod['modid'])) {
                            $cleanMods[] = $mod;
                        }
                    }
                    if (count($cleanMods) > 0) {
                        $sc['mods'] = $cleanMods;
                        $cleanScs[] = $sc;
                    }
                }
                if (count($cleanScs) > 0) {
                    $block['scs'] = $cleanScs;
                    $cleanStructure[] = $block;
                }
            }
            // Compléter toutes les UA demandées pour chaque modalité afin de garantir l'alignement
            foreach ($cleanStructure as $block) {
                foreach ($block['scs'] as $sc) {
                    foreach ($sc['mods'] as $mod) {
                        $scid = $mod['scid'];
                        $modid = $mod['modid'];
                        if (!isset($notesMap[$scid][$modid])) {
                            $notesMap[$scid][$modid] = [];
                        }
                        foreach ($uaNums as $ua) {
                            $uaStr = (string)$ua;
                            if (!isset($notesMap[$scid][$modid][$uaStr])) {
                                $notesMap[$scid][$modid][$uaStr] = '';
                            }
                        }
                    }
                }
            }
            $bulletins[] = [
                'eleve' => $eleve,
                'classe' => $classe,
                'notes' => $notesMap ?? [],
                'totauxSousComp' => $totauxSousComp ?? [],
                'totalGeneral' => $uaMoyennes ?? [],
                'moyenne' => $moyenne ?? 0,
                'structure' => $cleanStructure ?? [],
                'rangsUA' => [],
                'rangTrimestre' => 0,
                'profilClasse' => [],
                'rang' => 0
            ];
        }
        
        // Calcul des rangs par UA et par trimestre
        $rangsParUA = [];
        $rangsParTrimestre = [];
        
        // Collecter toutes les moyennes par UA
        foreach ($uaNums as $ua) {
            $moyennesUA = [];
            foreach ($bulletins as $i => $bulletin) {
                $moyennesUA[] = [
                    'index' => $i,
                    'moyenne' => $bulletin['totalGeneral'][$ua] ?? 0
                ];
            }
            // Trier par moyenne décroissante
            usort($moyennesUA, function($a, $b) {
                return $b['moyenne'] <=> $a['moyenne'];
            });
            // Attribuer les rangs
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
        
        // Collecter toutes les moyennes trimestrielles
        $moyennesTrimestre = [];
        foreach ($bulletins as $i => $bulletin) {
            $moyennesTrimestre[] = [
                'index' => $i,
                'moyenne' => $bulletin['moyenne'] ?? 0
            ];
        }
        // Trier par moyenne décroissante
        usort($moyennesTrimestre, function($a, $b) {
            return $b['moyenne'] <=> $a['moyenne'];
        });
        // Attribuer les rangs
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
        
        // Tri par mérite
        usort($bulletins, function($a, $b) {
            return ($b['moyenne'] ?? 0) <=> ($a['moyenne'] ?? 0);
        });
        // Correction : filtrer les moyennes valides
        $moyennes = array_filter(array_column($bulletins, 'moyenne'), function($m) {
            return is_numeric($m);
        });
        $nbMoyenne10 = count(array_filter($moyennes, function($m) { return $m >= 10; }));
        $moyenneClasse = count($moyennes) ? round(array_sum($moyennes)/count($moyennes),2) : 0;
        $moyennePremier = count($moyennes) ? max($moyennes) : 0;
        $moyenneDernier = count($moyennes) ? min($moyennes) : 0;
        $tauxReussite = count($moyennes) ? round($nbMoyenne10*100/count($moyennes),2) : 0;
        foreach ($bulletins as $i => &$b) {
            // Correction : garantir une moyenne numérique
            if (!isset($b['moyenne']) || !is_numeric($b['moyenne'])) {
                $b['moyenne'] = 0;
            }
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
}
