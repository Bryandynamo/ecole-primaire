<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Session;
use App\Models\Classe;
use App\Models\Note;
use App\Models\Evaluation;
use App\Models\SousCompetence;


// Configuration de base
$session_id = 1; // Remplace par ton session_id
$classe_id = 1;  // Remplace par ton classe_id
$evaluation = 1; // UA 1

echo "=== TEST STATISTIQUES SIMPLES (Corrigé) ===\n";
echo "Session ID: $session_id\n";
echo "Classe ID: $classe_id\n";
echo "Evaluation: $evaluation\n\n";

// Trouver l'évaluation
$evaluationModel = Evaluation::where('classe_id', $classe_id)
    ->where('session_id', $session_id)
    ->where('numero_eval', $evaluation)
    ->first();

if (!$evaluationModel) {
    echo "ERREUR: Évaluation non trouvée\n";
    exit;
}

echo "Evaluation trouvée: ID = {$evaluationModel->id}\n";

// Récupérer les notes pour cette évaluation
$notes = Note::where('session_id', $session_id)
    ->where('classe_id', $classe_id)
    ->where('evaluation_id', $evaluationModel->id)
    ->get();

echo "Nombre total de notes: " . $notes->count() . "\n";

// Récupérer les élèves de la classe et les indexer par ID pour un accès facile
$classe = Classe::with('eleves')->find($classe_id);
if (!$classe) {
    echo "ERREUR: Classe non trouvée\n";
    exit;
}
$elevesById = $classe->eleves->keyBy('id');

echo "Nombre d'élèves dans la classe: " . $elevesById->count() . "\n";
echo "Garçons: " . $classe->eleves->where('sexe', 'M')->count() . "\n";
echo "Filles: " . $classe->eleves->where('sexe', 'F')->count() . "\n\n";

// Calculer le total des points maximum pour l'évaluation
$sousCompetenceIds = $notes->pluck('sous_competence_id')->unique();
$sousCompetences = SousCompetence::whereIn('id', $sousCompetenceIds)->get();
$totalPointsMaxEvaluation = $sousCompetences->sum('points_max');

if ($totalPointsMaxEvaluation == 0) {
    echo "ERREUR: Le total des points maximum pour l'évaluation est 0. Vérifiez les sous-compétences.\n";
    exit;
}

echo "Total des points maximum pour cette évaluation: {$totalPointsMaxEvaluation}\n\n";

// Grouper les notes par élève pour un traitement unique
$notesParEleve = $notes->groupBy('eleve_id');

// Calculer les moyennes
$moyennes = [];
$ayant_compose = 0;
$admis = 0;
$echoues = 0;

echo "--- DÉTAIL DES MOYENNES PAR ÉLÈVE ---\n";
foreach ($notesParEleve as $eleveId => $notesEleve) {
    $eleve = $elevesById->get($eleveId);
    $nomEleve = $eleve ? $eleve->nom : "Élève Inconnu (ID: {$eleveId})";

    $totalPoints = $notesEleve->sum('valeur');
    
    $moyenneSur20 = round(($totalPoints / $totalPointsMaxEvaluation) * 20, 2);
    $moyennes[$eleveId] = $moyenneSur20;
    
    echo "Élève {$nomEleve}: {$totalPoints}/{$totalPointsMaxEvaluation} = {$moyenneSur20}/20\n";
    
    $ayant_compose++;
    if ($moyenneSur20 >= 10) {
        $admis++;
    } else {
        $echoues++;
    }
}

// Gérer les élèves qui n'ont pas de notes
$elevesSansNotes = $elevesById->keys()->diff($notesParEleve->keys());
foreach ($elevesSansNotes as $eleveId) {
    $eleve = $elevesById->get($eleveId);
    echo "Élève {$eleve->nom}: Aucune note\n";
}

echo "\n=== RÉSULTATS GLOBAUX ===\n";
echo "Ayant composé: $ayant_compose\n";
echo "Admis (moyenne >= 10/20): $admis\n";
echo "Échoués (moyenne < 10/20): $echoues\n";

if (count($moyennes) > 0) {
    $moyenne_generale = round(array_sum($moyennes) / count($moyennes), 2);
    echo "Moyenne générale de la classe: $moyenne_generale / 20\n";
    echo "Meilleure moyenne: " . max($moyennes) . " / 20\n";
    echo "Moins bonne moyenne: " . min($moyennes) . " / 20\n";
    
    if ($ayant_compose > 0) {
        $pourc_reussite = round($admis * 100 / $ayant_compose, 2);
        echo "Pourcentage de réussite: {$pourc_reussite}%\n";
    }
}

echo "\n";