<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Configuration des performances
    |--------------------------------------------------------------------------
    |
    | Ce fichier contient les paramètres d'optimisation des performances
    | pour l'application de gestion des bulletins.
    |
    */

    // Configuration du cache
    'cache' => [
        'registre_ttl' => env('REGISTRE_CACHE_TTL', 300), // 5 minutes
        'statistiques_ttl' => env('STATS_CACHE_TTL', 600), // 10 minutes
        'bulletin_ttl' => env('BULLETIN_CACHE_TTL', 1800), // 30 minutes
    ],

    // Configuration des requêtes
    'queries' => [
        'chunk_size' => env('QUERY_CHUNK_SIZE', 100),
        'timeout' => env('QUERY_TIMEOUT', 30),
        'max_memory' => env('MAX_MEMORY', '512M'),
        'max_execution_time' => env('MAX_EXECUTION_TIME', 300),
    ],

    // Configuration des exports
    'exports' => [
        'pdf' => [
            'memory_limit' => env('PDF_MEMORY_LIMIT', '512M'),
            'timeout' => env('PDF_TIMEOUT', 300),
            'chunk_size' => env('PDF_CHUNK_SIZE', 50),
        ],
        'excel' => [
            'memory_limit' => env('EXCEL_MEMORY_LIMIT', '256M'),
            'timeout' => env('EXCEL_TIMEOUT', 180),
            'chunk_size' => env('EXCEL_CHUNK_SIZE', 100),
        ],
    ],

    // Configuration de la validation
    'validation' => [
        'note_max_value' => env('NOTE_MAX_VALUE', 100),
        'note_min_value' => env('NOTE_MIN_VALUE', 0),
        'auto_save_delay' => env('AUTO_SAVE_DELAY', 500), // millisecondes
    ],

    // Configuration des logs
    'logging' => [
        'performance_log' => env('PERFORMANCE_LOG', true),
        'slow_query_threshold' => env('SLOW_QUERY_THRESHOLD', 1.0), // secondes
        'memory_threshold' => env('MEMORY_THRESHOLD', 100), // MB
    ],

    // Configuration de l'optimisation
    'optimization' => [
        'enable_query_cache' => env('ENABLE_QUERY_CACHE', true),
        'enable_result_cache' => env('ENABLE_RESULT_CACHE', true),
        'enable_compression' => env('ENABLE_COMPRESSION', true),
        'batch_size' => env('BATCH_SIZE', 50),
    ],
]; 