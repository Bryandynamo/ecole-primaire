<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(IllwareConsoleKernel::class);

$app->boot();

use App\Models\SousCompetence;
use App\Models\Lecon;

try {
    // Récupérer les 7 premières sous-compétences
    $sousCompetences = SousCompetence::take(7)->get();
    
    if ($sousCompetences->isEmpty()) {
        echo "Aucune sous-compétence trouvée dans la base de données.\n";
        exit(1);
    }
    
    $count = 0;
    
    foreach ($sousCompetences as $sc) {
        // Créer 3 leçons pour chaque sous-compétence
        for ($i = 1; $i <= 3; $i++) {
            Lecon::create([
                'sous_competence_id' => $sc->id,
                'nom' => 'Leçon ' . $i . ' - ' . $sc->nom,
                'total_a_couvrir_annee' => 10,
                'total_a_couvrir_trimestre' => 5,
                'total_a_couvrir_ua' => 2
            ]);
            $count++;
        }
    }
    
    echo "$count leçons ont été créées avec succès !\n";
    
} catch (\Exception $e) {
    echo "Erreur lors de la création des leçons : " . $e->getMessage() . "\n";
    exit(1);
}
