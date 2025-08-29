<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;

class CouvertureExport implements FromArray
{
    protected $classe;
    protected $evaluation;
    protected $sousCompetences;
    protected $lecons;
    protected $leconStats;
    protected $totalParDiscipline;

    public function __construct($classe, $evaluation, $sousCompetences, $lecons, $leconStats, $totalParDiscipline)
    {
        $this->classe = $classe;
        $this->evaluation = $evaluation;
        $this->sousCompetences = $sousCompetences;
        $this->lecons = $lecons; // grouped by sc id
        $this->leconStats = $leconStats;
        $this->totalParDiscipline = $totalParDiscipline;
    }

    public function array(): array
    {
        $rows = [];
        $rows[] = [
            'Fiche de couverture - Classe: ' . ($this->classe->nom ?? '') . ' | UA: ' . ($this->evaluation->numero_eval ?? $this->evaluation->nom)
        ];
        $rows[] = [
            'N°','Discipline','Leçon',
            "A couvrir UA","Non couverts (préc.)","Couverts UA","Taux UA %",
            "A couvrir Trimestre","Couverts Trimestre","Taux Trimestre %",
            "A couvrir Année","Couverts Année","Taux Année %"
        ];
        $num = 1;
        foreach ($this->sousCompetences as $sc) {
            $leconsPourSC = $this->lecons->get($sc->id, collect());
            foreach ($leconsPourSC as $index => $lecon) {
                $stats = $this->leconStats[$lecon->id] ?? [
                    'total_a_couvrir_ua' => 0,
                    'nb_non_couverts_precedent' => 0,
                    'nb_courant' => 0,
                    'taux_ua' => 0,
                    'total_trimestre' => 0,
                    'nb_couverts_trimestre' => 0,
                    'taux_trimestre' => 0,
                    'total_annee' => 0,
                    'nb_couverts_annee' => 0,
                    'taux_annee' => 0,
                ];
                $rows[] = [
                    $index === 0 ? $num : '',
                    $index === 0 ? ($sc->nom ?? '') : '',
                    $lecon->nom,
                    $stats['total_a_couvrir_ua'],
                    $stats['nb_non_couverts_precedent'],
                    $stats['nb_courant'],
                    round($stats['taux_ua'], 2),
                    $stats['total_trimestre'],
                    $stats['nb_couverts_trimestre'],
                    round($stats['taux_trimestre'], 2),
                    $stats['total_annee'],
                    $stats['nb_couverts_annee'],
                    round($stats['taux_annee'], 2),
                ];
            }
            $tot = $this->totalParDiscipline[$sc->id] ?? null;
            if ($tot) {
                $rows[] = [
                    '', 'TOTAL ' . ($sc->nom ?? ''), '',
                    $tot['total_prevu_ua'] ?? 0,
                    $tot['nb_non_couverts_precedent'] ?? 0,
                    $tot['nb_courant'] ?? 0,
                    round($tot['taux_ua'] ?? 0, 2),
                    $tot['total_trimestre'] ?? 0,
                    $tot['nb_couverts_trimestre'] ?? 0,
                    round($tot['taux_trimestre'] ?? 0, 2),
                    $tot['total_annee'] ?? 0,
                    $tot['nb_couverts_annee'] ?? 0,
                    round($tot['taux_annee'] ?? 0, 2),
                ];
            }
            $num++;
        }
        return $rows;
    }
}
