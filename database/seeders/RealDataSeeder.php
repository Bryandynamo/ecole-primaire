<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\Niveau;
use App\Models\Session;
use App\Models\Classe;
use App\Models\Enseignant;
use App\Models\Eleve;
use App\Models\Competence;
use App\Models\SousCompetence;
use App\Models\Modalite;
use App\Models\Evaluation;
use App\Models\SousCompetenceModalite;

class RealDataSeeder extends Seeder
{
    public function run(): void
    {
        // Les données d'établissement ont été supprimées car la table a été supprimée
        foreach ([
            ['id' => 1, 'nom' => 'Cours d’Initiation'],
            ['id' => 2, 'nom' => 'Cours Préparatoire'],
            ['id' => 3, 'nom' => 'Cours Élémentaire 1'],
            ['id' => 4, 'nom' => 'Cours Élémentaire 2'],
            ['id' => 5, 'nom' => 'Cours Moyen 1'],
            ['id' => 6, 'nom' => 'Cours Moyen 2'],
            ['id' => 7, 'nom' => 'CM2'],
        ] as $niveau) {
            Niveau::updateOrInsert(['id' => $niveau['id']], $niveau);
        }

        Session::updateOrInsert(['id' => 1], ['nom' => '2024/2025', 'date_debut' => '2024-09-01', 'date_fin' => '2025-06-30']);

        Classe::updateOrInsert(['id' => 1], ['nom' => 'CM2A', 'niveau_id' => 7, 'session_id' => 1]);

        Enseignant::updateOrInsert(['id' => 1], ['nom' => 'Ndongo', 'prenom' => 'Marie', 'matricule' => 'ENS010', 'classe_id' => 1, 'session_id' => 1]);

        foreach ([
            ['id' => 1, 'nom' => 'Tchoufa', 'prenom' => 'Brice', 'matricule' => 'ELV0001', 'sexe' => 'M', 'date_naissance' => '2014-02-01', 'classe_id' => 1, 'session_id' => 1],
            ['id' => 2, 'nom' => 'Manga', 'prenom' => 'Sylvie', 'matricule' => 'ELV0002', 'sexe' => 'F', 'date_naissance' => '2014-03-05', 'classe_id' => 1, 'session_id' => 1],
            ['id' => 3, 'nom' => 'Biloa', 'prenom' => 'Kevin', 'matricule' => 'ELV0003', 'sexe' => 'M', 'date_naissance' => '2014-01-15', 'classe_id' => 1, 'session_id' => 1],
            ['id' => 4, 'nom' => 'Essomba', 'prenom' => 'Chantal', 'matricule' => 'ELV0004', 'sexe' => 'F', 'date_naissance' => '2014-04-20', 'classe_id' => 1, 'session_id' => 1],
        ] as $eleve) {
            Eleve::updateOrInsert(['id' => $eleve['id']], $eleve);
        }

        foreach ([
            ['id' => 1, 'nom' => 'COMPETENCE1', 'description' => 'Langue et communication', 'niveau_id' => 7, 'points_max' => 40, 'session_id' => 1],
            ['id' => 2, 'nom' => 'COMPETENCE2', 'description' => 'Logique et calcul', 'niveau_id' => 7, 'points_max' => 40, 'session_id' => 1],
            ['id' => 3, 'nom' => 'COMPETENCE3', 'description' => 'Découverte du monde', 'niveau_id' => 7, 'points_max' => 40, 'session_id' => 1],
            ['id' => 4, 'nom' => 'COMPETENCE4', 'description' => 'Langues', 'niveau_id' => 1, 'points_max' => 100, 'session_id' => 1],
            ['id' => 5, 'nom' => 'COMPETENCE5', 'description' => 'Sciences', 'niveau_id' => 1, 'points_max' => 60, 'session_id' => 1],
            ['id' => 6, 'nom' => 'COMPETENCE6', 'description' => 'Sciences Sociales', 'niveau_id' => 1, 'points_max' => 40, 'session_id' => 1],
        ] as $competence) {
            Competence::updateOrInsert(['id' => $competence['id']], $competence);
        }

        foreach ([
            ['id' => 1, 'competence_id' => 1, 'nom' => '1A.FRANCAIS', 'points_max' => 40],
            ['id' => 2, 'competence_id' => 1, 'nom' => '1B.ANGLAIS', 'points_max' => 40],
            ['id' => 3, 'competence_id' => 1, 'nom' => '1C.LANGUE NATIONALE', 'points_max' => 20],
            ['id' => 4, 'competence_id' => 2, 'nom' => '2A.MATHEMATIQUE', 'points_max' => 30],
            ['id' => 5, 'competence_id' => 2, 'nom' => '2C.SCIENCE ET TECHNOLOGIE', 'points_max' => 30],
            ['id' => 6, 'competence_id' => 3, 'nom' => '3A.VALEUR SOCIAL(H/G)', 'points_max' => 20],
            ['id' => 7, 'competence_id' => 3, 'nom' => '3B.VALEUR CITOYENNE', 'points_max' => 20],
            ['id' => 8, 'competence_id' => 4, 'nom' => '4.DEVELOPPEMENT PERSONNEL', 'points_max' => 20],
            ['id' => 9, 'competence_id' => 5, 'nom' => '5.TIC', 'points_max' => 20],
            ['id' => 10, 'competence_id' => 6, 'nom' => '6A.EPS', 'points_max' => 20],
            ['id' => 11, 'competence_id' => 6, 'nom' => '6B.ACTIVITE ARTISTIQUE', 'points_max' => 20],
        ] as $sous_competence) {
            SousCompetence::updateOrInsert(['id' => $sous_competence['id']], $sous_competence);
        }

        foreach ([
            ['id' => 1, 'nom' => 'Orale', 'description' => 'Évaluation orale'],
            ['id' => 2, 'nom' => 'Écrite', 'description' => 'Évaluation écrite'],
            ['id' => 3, 'nom' => 'Pratique', 'description' => 'Évaluation pratique'],
            ['id' => 4, 'nom' => 'Savoir-faire', 'description' => 'Évaluation de savoir-faire'],
            ['id' => 5, 'nom' => 'Oral', 'description' => 'Évaluation orale'],
            ['id' => 6, 'nom' => 'written', 'description' => 'Évaluation écrite'],
            ['id' => 7, 'nom' => 'Attitude', 'description' => 'Évaluation pratique'],
        ] as $modalite) {
            Modalite::updateOrInsert(['id' => $modalite['id']], $modalite);
        }

        foreach ([
            ['id' => 1, 'enseignant_id' => 1, 'trimestre' => 1, 'session_id' => 1, 'classe_id' => 1, 'numero_eval' => 1],
            ['id' => 2, 'enseignant_id' => 1, 'trimestre' => 2, 'session_id' => 1, 'classe_id' => 1, 'numero_eval' => 2],
            ['id' => 3, 'enseignant_id' => 1, 'trimestre' => 3, 'session_id' => 1, 'classe_id' => 1, 'numero_eval' => 3],
        ] as $evaluation) {
            Evaluation::updateOrInsert(['id' => $evaluation['id']], $evaluation);
        }

        foreach ([
            ['id' => 1, 'sous_competence_id' => 1, 'modalite_id' => 1, 'points_max' => 20],
            ['id' => 2, 'sous_competence_id' => 1, 'modalite_id' => 2, 'points_max' => 15],
            ['id' => 4, 'sous_competence_id' => 1, 'modalite_id' => 4, 'points_max' => 5],
            ['id' => 12, 'sous_competence_id' => 2, 'modalite_id' => 5, 'points_max' => 20],
            ['id' => 13, 'sous_competence_id' => 2, 'modalite_id' => 6, 'points_max' => 15],
            ['id' => 14, 'sous_competence_id' => 2, 'modalite_id' => 7, 'points_max' => 5],
            ['id' => 15, 'sous_competence_id' => 3, 'modalite_id' => 1, 'points_max' => 10],
            ['id' => 16, 'sous_competence_id' => 3, 'modalite_id' => 2, 'points_max' => 5],
            ['id' => 17, 'sous_competence_id' => 3, 'modalite_id' => 3, 'points_max' => 3],
            ['id' => 18, 'sous_competence_id' => 3, 'modalite_id' => 4, 'points_max' => 2],
            ['id' => 22, 'sous_competence_id' => 4, 'modalite_id' => 1, 'points_max' => 5],
            ['id' => 23, 'sous_competence_id' => 4, 'modalite_id' => 2, 'points_max' => 20],
            ['id' => 25, 'sous_competence_id' => 4, 'modalite_id' => 4, 'points_max' => 5],
            ['id' => 29, 'sous_competence_id' => 5, 'modalite_id' => 1, 'points_max' => 5],
            ['id' => 30, 'sous_competence_id' => 5, 'modalite_id' => 2, 'points_max' => 5],
            ['id' => 31, 'sous_competence_id' => 5, 'modalite_id' => 3, 'points_max' => 15],
            ['id' => 32, 'sous_competence_id' => 5, 'modalite_id' => 4, 'points_max' => 5],
            ['id' => 36, 'sous_competence_id' => 6, 'modalite_id' => 1, 'points_max' => 3],
            ['id' => 37, 'sous_competence_id' => 6, 'modalite_id' => 2, 'points_max' => 3],
            ['id' => 38, 'sous_competence_id' => 6, 'modalite_id' => 3, 'points_max' => 10],
            ['id' => 39, 'sous_competence_id' => 6, 'modalite_id' => 4, 'points_max' => 4],
            ['id' => 43, 'sous_competence_id' => 7, 'modalite_id' => 1, 'points_max' => 5],
            ['id' => 44, 'sous_competence_id' => 7, 'modalite_id' => 2, 'points_max' => 5],
            ['id' => 45, 'sous_competence_id' => 7, 'modalite_id' => 3, 'points_max' => 8],
            ['id' => 46, 'sous_competence_id' => 7, 'modalite_id' => 4, 'points_max' => 2],
            ['id' => 47, 'sous_competence_id' => 8, 'modalite_id' => 1, 'points_max' => 5],
            ['id' => 48, 'sous_competence_id' => 8, 'modalite_id' => 2, 'points_max' => 3],
            ['id' => 49, 'sous_competence_id' => 8, 'modalite_id' => 3, 'points_max' => 10],
            ['id' => 50, 'sous_competence_id' => 8, 'modalite_id' => 4, 'points_max' => 2],
            ['id' => 51, 'sous_competence_id' => 9, 'modalite_id' => 1, 'points_max' => 3],
            ['id' => 52, 'sous_competence_id' => 9, 'modalite_id' => 2, 'points_max' => 3],
            ['id' => 53, 'sous_competence_id' => 9, 'modalite_id' => 3, 'points_max' => 10],
            ['id' => 54, 'sous_competence_id' => 9, 'modalite_id' => 4, 'points_max' => 4],
            ['id' => 55, 'sous_competence_id' => 10, 'modalite_id' => 1, 'points_max' => 3],
            ['id' => 56, 'sous_competence_id' => 10, 'modalite_id' => 2, 'points_max' => 3],
            ['id' => 57, 'sous_competence_id' => 10, 'modalite_id' => 3, 'points_max' => 10],
            ['id' => 58, 'sous_competence_id' => 10, 'modalite_id' => 4, 'points_max' => 4],
            ['id' => 59, 'sous_competence_id' => 11, 'modalite_id' => 1, 'points_max' => 4],
            ['id' => 60, 'sous_competence_id' => 11, 'modalite_id' => 2, 'points_max' => 3],
            ['id' => 61, 'sous_competence_id' => 11, 'modalite_id' => 3, 'points_max' => 10],
            ['id' => 62, 'sous_competence_id' => 11, 'modalite_id' => 4, 'points_max' => 2],
        ] as $item) {
            SousCompetenceModalite::updateOrInsert(['id' => $item['id']], $item);
        }
    }
}
