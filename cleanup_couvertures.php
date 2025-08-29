<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Lecon;
use App\Models\Couverture;
use App\Models\Evaluation;

echo "=== NETTOYAGE DES COUVERTURES ===\n";

$corrections = 0;
$couvertures = Couverture::all();

foreach ($couvertures as $couverture) {
    $lecon = Lecon::find($couverture->lecon_id);
    $evaluation = Evaluation::find($couverture->evaluation_id);
    
    if (!$lecon || !$evaluation) {
        continue;
    }
    
    // Récupérer les heures prévues pour cette UA
    $colonne = 'total_a_couvrir_ua' . $evaluation->numero_eval;
    $heures_prevues = $lecon->{$colonne} ?? 0;
    
    // Si les heures couvertes dépassent les heures prévues
    if ($couverture->nb_couverts > $heures_prevues && $heures_prevues > 0) {
        echo "Correction: Leçon '{$lecon->nom}' (UA{$evaluation->numero_eval})\n";
        echo "  Avant: {$couverture->nb_couverts} heures (max: {$heures_prevues})\n";
        
        // Corriger la valeur
        $couverture->nb_couverts = $heures_prevues;
        $couverture->save();
        
        echo "  Après: {$couverture->nb_couverts} heures\n";
        $corrections++;
    }
}

echo "\n=== RÉSULTAT ===\n";
echo "Nombre de corrections effectuées: $corrections\n";
echo "Nettoyage terminé !\n";
