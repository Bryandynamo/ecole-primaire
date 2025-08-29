-- Vider la table des leçons
TRUNCATE TABLE lecons;

-- Insérer des données de test pour chaque sous-compétence
INSERT INTO lecons (sous_competence_id, nom, total_a_couvrir_annee, total_a_couvrir_trimestre, total_a_couvrir_ua, created_at, updated_at)
SELECT 
    id,
    CONCAT('Leçon 1 - ', nom) as nom,
    10 as total_a_couvrir_annee,
    5 as total_a_couvrir_trimestre,
    2 as total_a_couvrir_ua,
    NOW() as created_at,
    NOW() as updated_at
FROM sous_competences
LIMIT 7;

-- Insérer une deuxième leçon pour chaque sous-compétence
INSERT INTO lecons (sous_competence_id, nom, total_a_couvrir_annee, total_a_couvrir_trimestre, total_a_couvrir_ua, created_at, updated_at)
SELECT 
    id,
    CONCAT('Leçon 2 - ', nom) as nom,
    15 as total_a_couvrir_annee,
    8 as total_a_couvrir_trimestre,
    3 as total_a_couvrir_ua,
    NOW() as created_at,
    NOW() as updated_at
FROM sous_competences
LIMIT 7;

-- Insérer une troisième leçon pour chaque sous-compétence
INSERT INTO lecons (sous_competence_id, nom, total_a_couvrir_annee, total_a_couvrir_trimestre, total_a_couvrir_ua, created_at, updated_at)
SELECT 
    id,
    CONCAT('Leçon 3 - ', nom) as nom,
    20 as total_a_couvrir_annee,
    10 as total_a_couvrir_trimestre,
    4 as total_a_couvrir_ua,
    NOW() as created_at,
    NOW() as updated_at
FROM sous_competences
LIMIT 7;
