<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class RegistreExport implements FromArray, WithEvents
{
    protected $session;
    protected $classe;
    protected $evaluation;
    protected $ordreCompetences;
    protected $eleves;
    protected $sousCompetenceIds;
    protected $modaliteIds;
    protected $pointsMaxMap;

    public function __construct($session, $classe, $evaluation, $ordreCompetences, $eleves, $sousCompetenceIds, $modaliteIds, $pointsMaxMap)
    {
        $this->session = $session;
        $this->classe = $classe;
        $this->evaluation = $evaluation;
        $this->ordreCompetences = $ordreCompetences;
        $this->eleves = $eleves;
        $this->sousCompetenceIds = $sousCompetenceIds;
        $this->modaliteIds = $modaliteIds;
        $this->pointsMaxMap = $pointsMaxMap;
    }

    public function array(): array
    {
        // 1. Prépare les entêtes multi-lignes (4 lignes)
        $header1 = ['SESSION ' . ($this->session->nom ?? '') . '   Evaluation numero: ______   mois de: ______'];
        $header2 = ['#', 'Matricule', 'Nom & Prénom'];
        $header3 = ['', '', ''];
        $header4 = ['', '', ''];
        $header5 = ['', '', ''];

        // Construction dynamique des entêtes fusionnées
        foreach ($this->ordreCompetences as $comp) {
            $colspanComp = 0;
            foreach ($comp['sous'] as $sc) {
                $colspanComp += count($sc['modalites']) + 1;
            }
            $header2[] = $comp['label'];
            for ($i = 1; $i < $colspanComp; $i++) $header2[] = '';
            foreach ($comp['sous'] as $sc) {
                $header3[] = $sc['label'];
                for ($i = 1; $i < count($sc['modalites']) + 1; $i++) $header3[] = '';
                foreach ($sc['modalites'] as $modalite) {
                    $header4[] = $modalite;
                }
                $header4[] = 'Total';
                foreach ($sc['modalites'] as $modalite) {
                    $header5[] = $this->pointsMaxMap[$sc['label']][$modalite] ?? '';
                }
                $header5[] = array_sum($this->pointsMaxMap[$sc['label']] ?? []);
            }
        }
        $header2 = array_merge($header2, ['Total', 'Moyenne', 'Cote', 'Rang']);
        $header3 = array_merge($header3, ['', '', '', '']);
        $header4 = array_merge($header4, ['', '', '', '']);
        $header5 = array_merge($header5, ['', '', '', '']);

        // 2. Prépare les données élèves (comme avant, mais sans HTML)
        $rows = [];
        // Calcul des rangs
        $moyennes = [];
        foreach ($this->eleves as $index => $eleve) {
            $grandTotalEleve = 0;
            $totalPointsMaxEleve = 0;
            $row = [$index + 1, $eleve->matricule, $eleve->nom.' '.$eleve->prenom];
            foreach ($this->ordreCompetences as $comp) {
                foreach ($comp['sous'] as $sc) {
                    $scId = $this->sousCompetenceIds[$sc['label']] ?? null;
                    $totalSousComp = 0;
                    foreach ($sc['modalites'] as $modaliteNom) {
                        $modaliteId = $this->modaliteIds[$modaliteNom] ?? null;
                        $val = '';
                        $pointsMax = $this->pointsMaxMap[$sc['label']][$modaliteNom] ?? 0;
                        if ($scId && $modaliteId && isset($eleve->notes_map[$scId]) && isset($eleve->notes_map[$scId][$modaliteId])) {
                            $val = $eleve->notes_map[$scId][$modaliteId];
                            if (is_numeric($val)) {
                                $totalSousComp += $val;
                            }
                        }
                        $row[] = $val;
                        $totalPointsMaxEleve += $pointsMax;
                    }
                    $row[] = $totalSousComp;
                    $grandTotalEleve += $totalSousComp;
                }
            }
            $row[] = $grandTotalEleve;
            $moyenne = $totalPointsMaxEleve > 0 ? ($grandTotalEleve / $totalPointsMaxEleve) * 20 : 0;
            $row[] = $totalPointsMaxEleve > 0 ? number_format($moyenne, 2) : '-';
            if ($moyenne >= 18) {
                $row[] = 'A+';
            } elseif ($moyenne >= 16) {
                $row[] = 'A';
            } elseif ($moyenne >= 14) {
                $row[] = 'B';
            } elseif ($moyenne >= 12) {
                $row[] = 'C';
            } elseif ($moyenne >= 10) {
                $row[] = 'D';
            } else {
                $row[] = 'E';
            }
            $moyennes[] = ['index' => $index, 'moyenne' => $moyenne];
            $rows[] = $row;
        }
        // Calcul des rangs
        usort($moyennes, function($a, $b) { return $b['moyenne'] <=> $a['moyenne']; });
        $rangs = [];
        foreach ($moyennes as $i => $data) {
            if ($i > 0 && $data['moyenne'] == $moyennes[$i-1]['moyenne']) {
                $rangs[$data['index']] = $rangs[$moyennes[$i-1]['index']];
            } else {
                $rangs[$data['index']] = $i + 1;
            }
        }
        // Ajoute le rang à chaque ligne
        foreach ($rows as $i => &$row) {
            $row[] = $rangs[$i];
        }

        // Assemble toutes les lignes
        return [
            $header1,
            $header2,
            $header3,
            $header4,
            $header5,
            ...$rows
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet;
                // Fusionne la première ligne sur toutes les colonnes
                $maxCol = $sheet->getHighestColumn();
                $sheet->mergeCells("A1:{$maxCol}1");
                // Fusionne les entêtes de compétences
                $rowComp = 2;
                $col = 4;
                foreach ($this->ordreCompetences as $comp) {
                    $colspanComp = 0;
                    foreach ($comp['sous'] as $sc) {
                        $colspanComp += count($sc['modalites']) + 1;
                    }
                    $startCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
                    $endCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col + $colspanComp - 1);
                    $sheet->mergeCells("{$startCol}{$rowComp}:{$endCol}{$rowComp}");
                    $col += $colspanComp;
                }
                // Fusionne les entêtes de sous-compétences
                $rowSousComp = 3;
                $col = 4;
                foreach ($this->ordreCompetences as $comp) {
                    foreach ($comp['sous'] as $sc) {
                        $colspanSC = count($sc['modalites']) + 1;
                        $startCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
                        $endCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col + $colspanSC - 1);
                        $sheet->mergeCells("{$startCol}{$rowSousComp}:{$endCol}{$rowSousComp}");
                        $col += $colspanSC;
                    }
                }
                // Fusionne les colonnes fixes
                $sheet->mergeCells('A2:A5');
                $sheet->mergeCells('B2:B5');
                $sheet->mergeCells('C2:C5');
                $sheet->mergeCells('D2:D5');
                $sheet->mergeCells('E2:E5');
                // Ajuste la largeur automatique
                foreach (range('A', $sheet->getHighestColumn()) as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }
                // Style en gras pour les entêtes
                for ($row = 1; $row <= 5; $row++) {
                    $sheet->getStyle("A{$row}:{$maxCol}{$row}")->getFont()->setBold(true);
                }
            }
        ];
    }

    public function view(): View
    {
        return view('registre.export-excel', [
            'session' => $this->session,
            'classe' => $this->classe,
            'trimestre' => $this->trimestre,
            'ordreCompetences' => $this->ordreCompetences,
            'eleves' => $this->eleves,
            'sousCompetenceIds' => $this->sousCompetenceIds,
            'modaliteIds' => $this->modaliteIds,
            'pointsMaxMap' => $this->pointsMaxMap,
            'pdfMode' => true // désactive les boutons/JS
        ]);
    }
}
