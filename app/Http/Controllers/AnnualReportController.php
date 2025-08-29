<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Classe;
use App\Models\Eleve;
use App\Models\Evaluation;
use App\Models\Note;
use App\Models\Competence;
use App\Models\SousCompetence;
use PDF;

class AnnualReportController extends Controller
{
    // Affiche le PDF annuel pour une classe donnée
    public function generateAnnualReports($classeId)
    {
        $classe = \App\Models\Classe::with('session')->findOrFail($classeId);
        $session_id = $classe->session_id;
        // Récupérer la liste des numéros d'UA (numero_eval), triée numériquement, pour la vue annuelle
        $uaNums = \App\Models\Evaluation::where('classe_id', $classeId)
            ->where('session_id', $session_id)
            ->orderBy('numero_eval')
            ->pluck('numero_eval')
            ->unique()
            ->sort()
            ->values()
            ->toArray();
        // Générer le bulletin annuel sur tous les UA (période = null pour forcer l'agrégation totale)
        $bulletins = \App\Services\BulletinOptimizationService::buildOptimizedBulletins($session_id, $classeId, null);
        // Déterminer l'année scolaire à afficher
        $annee = $classe->session->annee ?? '';
        set_time_limit(300);
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('bulletin.annuel-pdf', [
            'bulletins' => $bulletins,
            'classe' => $classe,
            'annee' => $annee,
            'uaNums' => $uaNums
        ])->setPaper('a4', 'landscape');
        $filename = 'bulletins_annuels_' . ($classe->nom ?? 'classe') . '_' . ($annee ?: date('Y')) . '.pdf';
        return $pdf->stream($filename);
    }
}
