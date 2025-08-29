<?php

namespace App\Services;

use App\Models\Note;

class BulletinBuilder
{
    protected $notes;
    protected $evaluationIds;

    public function __construct($notes, array $evaluationIds)
    {
        $this->notes = $notes;
        $this->evaluationIds = $evaluationIds;
    }

    /**
     * Build structured data for each student
     * return [ eleve_id => [ 'competences' => [...], 'moyenne' => float ] ]
     */
    public function build()
    {
        $perEleve = [];
        $allMoyennes = [];
        $allTotaux = [];
        // 1. Agréger notes et calculs par élève
        foreach ($this->notes as $note) {
            $eid = $note->eleve_id;
            $scId = $note->sous_competence_id;
            $modId = $note->modalite_id;
            $eval = $note->evaluation_id;
            $val = $note->valeur;
            if (!isset($perEleve[$eid])) {
                $perEleve[$eid] = [
                    'notes' => [],
                    'totauxSousComp' => [],
                    'totauxComp' => [],
                    'totalGeneral' => 0,
                    'count' => 0,
                    'moyenne' => null,
                ];
            }
            $perEleve[$eid]['notes'][$scId][$modId][$eval] = $val;
            $perEleve[$eid]['totalGeneral'] += $val;
            $perEleve[$eid]['count']++;
        }
        // 2. Calcul totaux par sous-compétence et compétence, et moyenne
        foreach ($perEleve as $eid => &$data) {
            $totauxSousComp = [];
            $totauxComp = [];
            // Parcours toutes les sous-compétences trouvées
            if (isset($data['notes'])) {
                foreach ($data['notes'] as $scId => $modalites) {
                    $totauxSousComp[$scId] = [];
                    // Initialiser pour chaque évaluation (UA)
                    foreach ($this->evaluationIds as $evId) {
                        $totauxSousComp[$scId][$evId] = 0;
                    }
                    $totalSC = 0;
                    foreach ($modalites as $modId => $evals) {
                        foreach ($evals as $evId => $note) {
                            if (isset($totauxSousComp[$scId][$evId])) {
                                $totauxSousComp[$scId][$evId] += $note;
                            } else {
                                $totauxSousComp[$scId][$evId] = $note;
                            }
                            $totalSC += $note;
                        }
                    }
                    $totauxComp[$scId] = $totalSC;
                }
            }
            $data['totauxSousComp'] = $totauxSousComp;
            $data['totauxComp'] = $totauxComp;
            $data['moyenne'] = $data['count'] ? round($data['totalGeneral'] / $data['count'], 2) : null;
            $allMoyennes[$eid] = $data['moyenne'];
            $allTotaux[$eid] = $data['totalGeneral'];
        }
        
        // Calcul des rangs par UA
        $rangsParUA = [];
        foreach ($this->evaluationIds as $evalId) {
            $moyennesUA = [];
            foreach ($perEleve as $eid => $data) {
                $totalUA = 0;
                $countUA = 0;
                foreach ($data['notes'] as $scId => $modalites) {
                    foreach ($modalites as $modId => $evals) {
                        if (isset($evals[$evalId])) {
                            $totalUA += $evals[$evalId];
                            $countUA++;
                        }
                    }
                }
                $moyenneUA = $countUA > 0 ? round($totalUA / $countUA, 2) : 0;
                $moyennesUA[] = [
                    'eid' => $eid,
                    'moyenne' => $moyenneUA
                ];
            }
            // Trier par moyenne décroissante
            usort($moyennesUA, function($a, $b) {
                return $b['moyenne'] <=> $a['moyenne'];
            });
            // Attribuer les rangs
            $rangsParUA[$evalId] = [];
            $currentRank = 1;
            $lastMoyenne = null;
            foreach ($moyennesUA as $i => $item) {
                if ($lastMoyenne !== null && $item['moyenne'] < $lastMoyenne) {
                    $currentRank = $i + 1;
                }
                $rangsParUA[$evalId][$item['eid']] = $currentRank;
                $lastMoyenne = $item['moyenne'];
            }
        }
        
        // 3. Calcul rangs et statistiques de classe
        arsort($allMoyennes);
        $rangs = array_keys($allMoyennes);
        $moyenneClasse = count($allMoyennes) ? round(array_sum($allMoyennes)/count($allMoyennes),2) : 0;
        $moyennePremier = count($allMoyennes) ? max($allMoyennes) : 0;
        $moyenneDernier = count($allMoyennes) ? min($allMoyennes) : 0;
        $nbMoyenne10 = count(array_filter($allMoyennes, fn($m) => $m >= 10));
        $tauxReussite = count($allMoyennes) ? round($nbMoyenne10*100/count($allMoyennes),2) : 0;
        // 4. Injecter profil de classe et rang dans chaque élève
        foreach ($perEleve as $eid => &$data) {
            $data['rang'] = array_search($eid, $rangs) + 1;
            $data['rangsUA'] = [];
            foreach ($this->evaluationIds as $evalId) {
                $data['rangsUA'][$evalId] = $rangsParUA[$evalId][$eid] ?? 0;
            }
            $data['profilClasse'] = [
                'moyenne_10' => $nbMoyenne10,
                'moyenne_classe' => $moyenneClasse,
                'taux_reussite' => $tauxReussite,
                'moyenne_premier' => $moyennePremier,
                'moyenne_dernier' => $moyenneDernier,
            ];
        }
        return $perEleve;
    }
}
