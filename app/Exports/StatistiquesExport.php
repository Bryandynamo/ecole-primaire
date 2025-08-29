<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;

class StatistiquesExport implements FromArray
{
    protected $classe;
    protected $session;
    protected $periode;
    protected $type;
    protected $statistiques;
    protected $statsUAs;
    protected $recapEvaluations;

    public function __construct($classe, $session, $periode, $type, $statistiques, $statsUAs = [], $recapEvaluations = [])
    {
        $this->classe = $classe;
        $this->session = $session;
        $this->periode = $periode;
        $this->type = $type;
        $this->statistiques = $statistiques;
        $this->statsUAs = $statsUAs ?? [];
        $this->recapEvaluations = $recapEvaluations ?? [];
    }

    public function array(): array
    {
        $rows = [];
        // Titre
        [$titre, $libelle] = $this->computeTitles();
        $rows[] = [$titre];
        $rows[] = [
            'Classe: ' . ($this->classe->nom ?? ''),
            'Année scolaire: ' . ($this->session->nom ?? ''),
            'Période: ' . $libelle
        ];
        $rows[] = [];

        // Bloc statistiques principales
        $rows[] = ['Catégorie', 'Classe', 'Garçons', 'Filles'];
        $s = is_array($this->statistiques) ? $this->statistiques : [];
        if (empty($s)) {
            // Aucune donnée, on met une ligne informative et on retourne
            $rows[] = ['Aucune donnée disponible pour cette sélection', '', '', ''];
            return $rows;
        }
        $rows[] = ['Inscrits', $s['inscrits'] ?? 0, $s['inscrits_garcons'] ?? 0, $s['inscrits_filles'] ?? 0];
        $rows[] = ['Ayant composé', $s['ayant_compose'] ?? 0, $s['ayant_compose_garcons'] ?? 0, $s['ayant_compose_filles'] ?? 0];
        $rows[] = ['Admis (moy ≥ 10)', $s['admis'] ?? 0, $s['admis_garcons'] ?? 0, $s['admis_filles'] ?? 0];
        $rows[] = ['Échoués (moy < 10)', $s['echoues'] ?? 0, $s['echoues_garcons'] ?? 0, $s['echoues_filles'] ?? 0];
        $rows[] = ['% Réussite', ($s['pourc_reussite'] ?? 0) . '%', ($s['pourc_reussite_garcons'] ?? 0) . '%', ($s['pourc_reussite_filles'] ?? 0) . '%'];
        $rows[] = ['% Échec', ($s['pourc_echec'] ?? 0) . '%', ($s['pourc_echec_garcons'] ?? 0) . '%', ($s['pourc_echec_filles'] ?? 0) . '%'];
        $rows[] = ['Moyenne générale', $s['moyenne_generale'] ?? '-', $s['moyenne_generale_garcons'] ?? '-', $s['moyenne_generale_filles'] ?? '-'];
        $rows[] = ['Moyenne du premier', $s['moyenne_premier'] ?? '-', $s['moyenne_premier_garcons'] ?? '-', $s['moyenne_premier_filles'] ?? '-'];
        $rows[] = ['Moyenne du dernier', $s['moyenne_dernier'] ?? '-', $s['moyenne_dernier_garcons'] ?? '-', $s['moyenne_dernier_filles'] ?? '-'];

        // Section récapitulatif par évaluation (si disponible)
        if ($this->type === 'trimestre') {
            $rows[] = [];
            $rows[] = ['Récapitulatif par évaluation (trimestre)'];
            $rows[] = ['Évaluation', 'Moyenne générale', '% Réussite', 'Admis', 'Échoués', 'Ayant composé'];
            // On suppose que $periode est le numéro du trimestre (1..3)
            $debutUA = ((int)$this->periode - 1) * 3 + 1;
            $finUA = (int)$this->periode * 3;
            for ($ua = $debutUA; $ua <= $finUA; $ua++) {
                $st = $this->statsUAs[$ua] ?? null;
                if ($st) {
                    $rows[] = [
                        'UA ' . $ua,
                        $st['moyenne_generale'] ?? '-',
                        ($st['pourc_reussite'] ?? '-') . '%',
                        $st['admis'] ?? '-',
                        $st['echoues'] ?? '-',
                        $st['ayant_compose'] ?? '-',
                    ];
                } else {
                    $rows[] = ['UA ' . $ua, 'Aucune donnée'];
                }
            }
        }

        if ($this->type !== 'trimestre' && !empty($this->recapEvaluations)) {
            $rows[] = [];
            $rows[] = ['Récapitulatif par évaluation'];
            $rows[] = ['Évaluation', 'Moyenne générale', '% Réussite', 'Admis', 'Échoués', 'Ayant composé'];
            foreach ($this->recapEvaluations as $recap) {
                $st = $recap['stats'] ?? [];
                $rows[] = [
                    'UA ' . ($recap['numero_eval'] ?? ''),
                    $st['moyenne_generale'] ?? '-',
                    ($st['pourc_reussite'] ?? '-') . '%',
                    $st['admis'] ?? '-',
                    $st['echoues'] ?? '-',
                    $st['ayant_compose'] ?? '-',
                ];
            }
        }

        return $rows;
    }

    private function computeTitles(): array
    {
        $libelle = '';
        $titre = '';
        if ($this->periode === 'annee') {
            $libelle = "toute l'année";
            $titre = 'Statistiques annuelles';
        } elseif ($this->type === 'trimestre') {
            $tr = (int)$this->periode;
            if ($tr == 1) $libelle = '1er trimestre';
            elseif ($tr == 2) $libelle = '2e trimestre';
            elseif ($tr == 3) $libelle = '3e trimestre';
            else $libelle = $tr . 'e trimestre';
            $titre = 'Statistiques du ' . $libelle;
        } else {
            $ua = (int)$this->periode;
            if ($ua == 1) $libelle = '1ère évaluation';
            elseif ($ua == 2) $libelle = '2e évaluation';
            elseif ($ua == 3) $libelle = '3e évaluation';
            else $libelle = $ua . 'e évaluation';
            $titre = 'Statistiques de ' . $libelle;
        }
        return [$titre, $libelle];
    }
}
