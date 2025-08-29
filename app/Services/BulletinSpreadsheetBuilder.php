<?php

namespace App\Services;

if (!function_exists('cote')) {
    function cote($n) {
        if(!is_numeric($n)) return '';
        return $n>=18?'A+':($n>=16?'A':($n>=14?'B+':($n>=12?'B':($n>=10?'C':'D'))));
    }
}

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Illuminate\Support\Collection;

class BulletinSpreadsheetBuilder
{
    /**
     * Build a Spreadsheet bulletin for a class.
     *
     * @param \App\Models\Classe $classe
     * @param array $evalIds
     * @param array $dataParEleve  // résultat BulletinBuilder->build()
     * @return Spreadsheet
     */
    public function build($classe, array $evalIds, array $dataParEleve): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $spreadsheet->removeSheetByIndex(0);

        foreach ($classe->eleves as $eleve) {
            $sheet = new Worksheet($spreadsheet, $eleve->nom.' '.$eleve->prenom);
            $spreadsheet->addSheet($sheet);
            $this->renderHeader($sheet, $classe, $eleve);
            $this->renderTable($sheet, $classe, $evalIds, $dataParEleve[$eleve->id] ?? []);
        }

        $spreadsheet->setActiveSheetIndex(0);
        return $spreadsheet;
    }

    private function renderHeader(Worksheet $sheet, $classe, $eleve): void
    {
        // République header
        $sheet->mergeCells('A1:I1')->setCellValue('A1', 'REPUBLIQUE DU CAMEROUN — PAIX • TRAVAIL • PATRIE');
        $sheet->mergeCells('A2:I2')->setCellValue('A2', 'MINISTERE DE L\'EDUCATION DE BASE');
        $sheet->mergeCells('A3:I3')->setCellValue('A3', 'ECOLE PUBLIQUE DE ______');

        $sheet->getStyle('A1:A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A1:A3')->getFont()->setBold(true);

        // Élève info
        $sheet->setCellValue('A5', 'Nom Prénom :');
        $sheet->mergeCells('B5:I5')->setCellValue('B5', $eleve->nom.' '.$eleve->prenom);
        $sheet->setCellValue('A6', 'Classe :');
        $sheet->setCellValue('B6', $classe->nom);
    }

    private function renderTable(Worksheet $sheet, $classe, array $evalIds, array $eleveData): void
    {
        // define starting row
        $row = 8;
        // Header row 1
        $sheet->mergeCells("A{$row}:A".($row+1))->setCellValue("A{$row}", 'COMPETENCES');
        $sheet->mergeCells("B{$row}:B".($row+1))->setCellValue("B{$row}", 'SOUS COMPETENCES');
        $sheet->mergeCells("C{$row}:J{$row}")->setCellValue("C{$row}", 'TRIMESTRE – 1er TRIMESTRE');
        $row++;
        // Header row 2
        $sheet->setCellValue("C{$row}", 'UNITES\nNOTES');
        $col = 'D';
        $headers = [];
        foreach ($evalIds as $ua) {
            $headers[] = ["{$ua}", 'Notes'];
            $headers[] = ["{$ua}", 'Cote'];
        }
        foreach ($headers as $h) {
            $sheet->setCellValue("{$col}{$row}", $h[0]."\n".$h[1]);
            $sheet->getColumnDimension($col)->setWidth(6);
            $sheet->getStyle("{$col}{$row}")->getAlignment()->setWrapText(true);
            $col++;
        }
        $sheet->setCellValue("{$col}{$row}", 'Total');
        $sheet->getColumnDimension($col)->setWidth(8);

        // freeze header style
        $sheet->getStyle("A".($row-1).":{$col}{$row}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle("A".($row-1).":{$col}{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);

        $row++;
        // Data rows
        $totalEleve = 0;
        foreach ($classe->niveau->competences as $comp) {
            $startRowComp = $row;
            $compTotal = 0;
            foreach ($comp->sousCompetences as $sc) {
                foreach ($sc->modalites as $idx => $mod) {
                    $sheet->setCellValue("B{$row}", $mod->nom);
                    // notes per eval
                    $colCursor = 'D';
                    $rowTotal = 0;
                    foreach ($evalIds as $evId) {
                        $note = $eleveData['notes'][$sc->id][$mod->id][$evId] ?? null;
                        $sheet->setCellValue("{$colCursor}{$row}", $note);
                        $colCursor++;
                        $sheet->setCellValue("{$colCursor}{$row}", $note!==null?cote($note):null);
                        $colCursor++;
                        $rowTotal += $note ?? 0;
                    }
                    $sheet->setCellValue("{$colCursor}{$row}", $rowTotal);
                    $compTotal += $rowTotal;
                    $row++;
                }
            }
            // Total competence row
            $sheet->mergeCells("A{$row}:B{$row}")->setCellValue("A{$row}", 'Total '.$comp->nom);
            $sheet->setCellValue("{$colCursor}{$row}", $compTotal);
            $sheet->getStyle("A{$row}:{$colCursor}{$row}")->getFont()->setBold(true);
            $sheet->getStyle("A{$row}:{$colCursor}{$row}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $totalEleve += $compTotal;
            $row++;
        }
        // Résumé élève
        $sheet->mergeCells("A{$row}:B{$row}")->setCellValue("A{$row}", 'Total points élève');
        $sheet->setCellValue("C{$row}", $totalEleve);
    }
}
