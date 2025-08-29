<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Illuminate\Support\Collection;

class BulletinExcelExporter
{
    private string $templatePath;

    public function __construct(string $templatePath = null)
    {
        $this->templatePath = $templatePath ?? storage_path('app/bulletin_template.xlsx');
    }

    /**
     * @param array $evalIds
     * @param \Illuminate\Database\Eloquent\Collection $eleves
     * @param array $dataParEleve  // construit par BulletinBuilder::build()
     * @return Spreadsheet
     */
    public function build(array $evalIds, Collection $eleves, array $dataParEleve): Spreadsheet
    {
        $spreadsheet = IOFactory::load($this->templatePath);
        foreach ($eleves as $eleve) {
            $this->fillEleveSheet($spreadsheet->getActiveSheet(), $eleve, $evalIds, $dataParEleve[$eleve->id] ?? []);
        }
        return $spreadsheet;
    }

    private function fillEleveSheet(Worksheet $sheet, $eleve, array $evalIds, array $eleveData): void
    {
        // Identité élève
        $sheet->setCellValue('E6', $eleve->nom.' '.$eleve->prenom);
        // Exemple de remplissage d'une note : rechercher cellule nommée
        if (!isset($eleveData['notes'])) return;
        foreach ($eleveData['notes'] as $scId => $modalites) {
            foreach ($modalites as $modaliteId => $evalVals) {
                foreach ($evalIds as $ev) {
                    if (!isset($evalVals[$ev])) continue;
                    $note = $evalVals[$ev];
                    $cell = $this->cellName('N', $scId, $ev); // convention N_SCID_EVALID
                    if ($sheet->getParent()->getNamedRange($cell)) {
                        $sheet->setCellValue($cell, $note);
                        $sheet->setCellValue(str_replace('N_', 'C_', $cell), cote($note));
                    }
                }
            }
        }
        // Moyenne / total etc.
        $sheet->setCellValue('S40', $eleveData['moyenne'] ?? '');
    }

    private function cellName(string $prefix, int $scId, int $evalId): string
    {
        return $prefix.'_'.$scId.'_'.$evalId; // example: N_12_1
    }
}
