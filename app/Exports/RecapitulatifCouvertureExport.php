<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;

class RecapitulatifCouvertureExport implements FromArray
{
    protected $classe;
    protected $session;
    protected $recapData;
    protected $uasByTrimestre; // collection grouped by trimestre

    public function __construct($classe, $session, $recapData, $uasByTrimestre)
    {
        $this->classe = $classe;
        $this->session = $session;
        $this->recapData = $recapData;
        $this->uasByTrimestre = $uasByTrimestre;
    }

    public function array(): array
    {
        $rows = [];
        $rows[] = [
            'Récapitulatif de la couverture – Session: ' . ($this->session->nom ?? '') . ' – Classe: ' . ($this->classe->nom ?? '')
        ];
        // Header
        $header = ['N°', 'Discipline (Sous-compétence)', 'Leçon'];
        foreach ($this->uasByTrimestre as $trimestre => $uas) {
            foreach ($uas as $ua) {
                $header[] = 'UA' . $ua->numero_eval;
            }
            $header[] = 'Total T' . $trimestre;
        }
        $header[] = 'Total Annuel';
        $rows[] = $header;

        $num = 1;
        foreach ($this->recapData as $scId => $data) {
            if (count($data['lecons']) === 0) continue;
            foreach ($data['lecons'] as $index => $lecon) {
                $row = [];
                $row[] = $index === 0 ? $num : '';
                $row[] = $index === 0 ? $data['nom'] : '';
                $row[] = $lecon['nom'];
                foreach ($this->uasByTrimestre as $trimestre => $uas) {
                    foreach ($uas as $ua) {
                        $row[] = $lecon['totaux']['ua_' . $ua->numero_eval] ?? 0;
                    }
                    $row[] = $lecon['totaux']['trimestre_' . $trimestre] ?? 0;
                }
                $row[] = $lecon['totaux']['annuel'] ?? 0;
                $rows[] = $row;
            }
            $num++;
        }
        return $rows;
    }
}
