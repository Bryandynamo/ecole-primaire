<?php

namespace App\Services;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;

class ValidationService
{
    /**
     * Règles de validation communes
     */
    private const COMMON_RULES = [
        'session_id' => 'required|integer|exists:sessions,id',
        'classe_id' => 'required|integer|exists:classes,id',
        'evaluation_id' => 'required|integer|exists:evaluations,id',
        'eleve_id' => 'required|integer|exists:eleves,id',
        'sous_competence_id' => 'required|integer|exists:sous_competences,id',
        'modalite_id' => 'required|integer|exists:modalites,id',
        'valeur' => 'nullable|numeric|min:0|max:100',
        'periode' => 'nullable|string|max:50',
        'trimestre' => 'nullable|integer|min:1|max:3',
    ];

    /**
     * Messages d'erreur personnalisés
     */
    private const CUSTOM_MESSAGES = [
        'session_id.required' => 'L\'identifiant de la session est requis.',
        'session_id.exists' => 'La session spécifiée n\'existe pas.',
        'classe_id.required' => 'L\'identifiant de la classe est requis.',
        'classe_id.exists' => 'La classe spécifiée n\'existe pas.',
        'evaluation_id.required' => 'L\'identifiant de l\'évaluation est requis.',
        'evaluation_id.exists' => 'L\'évaluation spécifiée n\'existe pas.',
        'eleve_id.required' => 'L\'identifiant de l\'élève est requis.',
        'eleve_id.exists' => 'L\'élève spécifié n\'existe pas.',
        'valeur.numeric' => 'La valeur doit être un nombre.',
        'valeur.min' => 'La valeur ne peut pas être négative.',
        'valeur.max' => 'La valeur ne peut pas dépasser 100.',
        'trimestre.min' => 'Le trimestre doit être entre 1 et 3.',
        'trimestre.max' => 'Le trimestre doit être entre 1 et 3.',
    ];

    /**
     * Valider les données de notes
     */
    public function validateNoteData(array $data): array
    {
        $rules = [
            'session_id' => self::COMMON_RULES['session_id'],
            'classe_id' => self::COMMON_RULES['classe_id'],
            'evaluation_id' => self::COMMON_RULES['evaluation_id'],
            'eleve_id' => self::COMMON_RULES['eleve_id'],
            'sous_competence_id' => self::COMMON_RULES['sous_competence_id'],
            'modalite_id' => self::COMMON_RULES['modalite_id'],
            'valeur' => self::COMMON_RULES['valeur'],
        ];

        return $this->validate($data, $rules, 'validation des notes');
    }

    /**
     * Valider les paramètres de statistiques
     */
    public function validateStatistiquesParams(array $data): array
    {
        $rules = [
            'session_id' => self::COMMON_RULES['session_id'],
            'classe_id' => self::COMMON_RULES['classe_id'],
            'periode' => self::COMMON_RULES['periode'],
        ];

        return $this->validate($data, $rules, 'validation des paramètres de statistiques');
    }

    /**
     * Valider les paramètres de bulletin
     */
    public function validateBulletinParams(array $data): array
    {
        $rules = [
            'session_id' => self::COMMON_RULES['session_id'],
            'classe_id' => self::COMMON_RULES['classe_id'],
            'evaluation_id' => 'nullable|integer|exists:evaluations,id',
            'trimestre' => 'nullable|integer|min:1|max:3',
        ];

        return $this->validate($data, $rules, 'validation des paramètres de bulletin');
    }

    /**
     * Valider les données d'élève
     */
    public function validateEleveData(array $data): array
    {
        $rules = [
            'nom' => 'required|string|max:255|regex:/^[a-zA-ZÀ-ÿ\s\'-]+$/',
            'prenom' => 'required|string|max:255|regex:/^[a-zA-ZÀ-ÿ\s\'-]+$/',
            'sexe' => 'required|in:M,F',
            'date_naissance' => 'required|date|before:today',
            'classe_id' => self::COMMON_RULES['classe_id'],
        ];

        $messages = [
            'nom.required' => 'Le nom est requis.',
            'nom.regex' => 'Le nom ne peut contenir que des lettres, espaces, tirets et apostrophes.',
            'prenom.required' => 'Le prénom est requis.',
            'prenom.regex' => 'Le prénom ne peut contenir que des lettres, espaces, tirets et apostrophes.',
            'sexe.required' => 'Le sexe est requis.',
            'sexe.in' => 'Le sexe doit être M ou F.',
            'date_naissance.required' => 'La date de naissance est requise.',
            'date_naissance.before' => 'La date de naissance doit être antérieure à aujourd\'hui.',
        ];

        return $this->validate($data, $rules, 'validation des données d\'élève', $messages);
    }

    /**
     * Valider les données de classe
     */
    public function validateClasseData(array $data): array
    {
        $rules = [
            'nom' => 'required|string|max:255|unique:classes,nom',
            'niveau_id' => 'required|integer|exists:niveaux,id',
            'enseignant_id' => 'nullable|integer|exists:enseignants,id',
        ];

        $messages = [
            'nom.required' => 'Le nom de la classe est requis.',
            'nom.unique' => 'Une classe avec ce nom existe déjà.',
            'niveau_id.required' => 'Le niveau est requis.',
            'niveau_id.exists' => 'Le niveau spécifié n\'existe pas.',
        ];

        return $this->validate($data, $rules, 'validation des données de classe', $messages);
    }

    /**
     * Valider les données d'évaluation
     */
    public function validateEvaluationData(array $data): array
    {
        $rules = [
            'numero_eval' => 'required|integer|min:1|max:9',
            'nom' => 'required|string|max:255',
            'classe_id' => self::COMMON_RULES['classe_id'],
            'session_id' => self::COMMON_RULES['session_id'],
            'date_evaluation' => 'required|date',
        ];

        $messages = [
            'numero_eval.required' => 'Le numéro d\'évaluation est requis.',
            'numero_eval.min' => 'Le numéro d\'évaluation doit être entre 1 et 9.',
            'numero_eval.max' => 'Le numéro d\'évaluation doit être entre 1 et 9.',
            'nom.required' => 'Le nom de l\'évaluation est requis.',
            'date_evaluation.required' => 'La date d\'évaluation est requise.',
        ];

        return $this->validate($data, $rules, 'validation des données d\'évaluation', $messages);
    }

    /**
     * Valider les données de compétence
     */
    public function validateCompetenceData(array $data): array
    {
        $rules = [
            'nom' => 'required|string|max:255',
            'niveau_id' => 'required|integer|exists:niveaux,id',
            'description' => 'nullable|string|max:1000',
        ];

        $messages = [
            'nom.required' => 'Le nom de la compétence est requis.',
            'niveau_id.required' => 'Le niveau est requis.',
            'niveau_id.exists' => 'Le niveau spécifié n\'existe pas.',
        ];

        return $this->validate($data, $rules, 'validation des données de compétence', $messages);
    }

    /**
     * Valider les données de sous-compétence
     */
    public function validateSousCompetenceData(array $data): array
    {
        $rules = [
            'nom' => 'required|string|max:255',
            'competence_id' => 'required|integer|exists:competences,id',
            'description' => 'nullable|string|max:1000',
        ];

        $messages = [
            'nom.required' => 'Le nom de la sous-compétence est requis.',
            'competence_id.required' => 'La compétence est requise.',
            'competence_id.exists' => 'La compétence spécifiée n\'existe pas.',
        ];

        return $this->validate($data, $rules, 'validation des données de sous-compétence', $messages);
    }

    /**
     * Valider les données de modalité
     */
    public function validateModaliteData(array $data): array
    {
        $rules = [
            'nom' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'points_max' => 'nullable|numeric|min:0|max:100',
        ];

        $messages = [
            'nom.required' => 'Le nom de la modalité est requis.',
            'points_max.numeric' => 'Les points maximum doivent être un nombre.',
            'points_max.min' => 'Les points maximum ne peuvent pas être négatifs.',
            'points_max.max' => 'Les points maximum ne peuvent pas dépasser 100.',
        ];

        return $this->validate($data, $rules, 'validation des données de modalité', $messages);
    }

    /**
     * Méthode générique de validation
     */
    private function validate(array $data, array $rules, string $context, array $customMessages = []): array
    {
        try {
            $messages = array_merge(self::CUSTOM_MESSAGES, $customMessages);
            
            $validator = Validator::make($data, $rules, $messages);
            
            if ($validator->fails()) {
                Log::warning("Validation échouée pour {$context}", [
                    'data' => $data,
                    'errors' => $validator->errors()->toArray()
                ]);
                
                throw new ValidationException($validator);
            }
            
            return $validator->validated();
            
        } catch (ValidationException $e) {
            Log::error("Erreur de validation pour {$context}", [
                'data' => $data,
                'errors' => $e->errors()
            ]);
            throw $e;
        } catch (Exception $e) {
            Log::error("Erreur inattendue lors de la validation pour {$context}", [
                'data' => $data,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Valider les données de recherche
     */
    public function validateSearchData(array $data): array
    {
        $rules = [
            'query' => 'nullable|string|max:255',
            'session_id' => 'nullable|integer|exists:sessions,id',
            'classe_id' => 'nullable|integer|exists:classes,id',
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:100',
        ];

        return $this->validate($data, $rules, 'validation des données de recherche');
    }

    /**
     * Valider les données d'export
     */
    public function validateExportData(array $data): array
    {
        $rules = [
            'session_id' => self::COMMON_RULES['session_id'],
            'classe_id' => self::COMMON_RULES['classe_id'],
            'evaluation_id' => 'nullable|integer|exists:evaluations,id',
            'format' => 'nullable|in:pdf,excel,csv',
            'include_headers' => 'nullable|boolean',
        ];

        return $this->validate($data, $rules, 'validation des données d\'export');
    }

    /**
     * Valider les données de configuration
     */
    public function validateConfigData(array $data): array
    {
        $rules = [
            'app_name' => 'required|string|max:255',
            'app_url' => 'required|url',
            'database_host' => 'required|string|max:255',
            'database_name' => 'required|string|max:255',
            'database_username' => 'required|string|max:255',
            'mail_host' => 'nullable|string|max:255',
            'mail_port' => 'nullable|integer|min:1|max:65535',
        ];

        return $this->validate($data, $rules, 'validation des données de configuration');
    }
} 