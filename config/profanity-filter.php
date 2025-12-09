<?php

return [
    'default_language' => 'id',  // Tambah key ini di root config
    /*
    |--------------------------------------------------------------------------
    | Supported Languages
    |--------------------------------------------------------------------------
    |
    | The list of supported languages for profanity filtering.
    |
    */
    'supported_languages' => ['en', 'fr', 'ar', 'id'],  // Tambah 'id' di sini (opsional, tapi recommended)

    /*
    |--------------------------------------------------------------------------
    | Languages Match Without Boundaries
    |--------------------------------------------------------------------------
    ...
    */
    'languages_match_without_boundaries' => ['ar'],

    /*
    |--------------------------------------------------------------------------
    | Replacement Character
    |--------------------------------------------------------------------------
    ...
    */
    'replacement_character' => '***',  // Ubah ke '***' untuk lebih visible (default '*')

    /*
    |--------------------------------------------------------------------------
    | Case Sensitive
    |--------------------------------------------------------------------------
    ...
    */
    'case_sensitive' => false,

    /*
    |--------------------------------------------------------------------------
    | Detect Leet Speak
    |--------------------------------------------------------------------------
    ...
    */
    'detect_leet_speak' => true,

    /*
    |--------------------------------------------------------------------------
    | Custom Words
    |--------------------------------------------------------------------------
    ...
    */
    'custom_words' => [
        'en' => [],
        'fr' => [],
        'ar' => [],
        'id' => [  // Tambah ini: Daftar kata kasar Indonesia
            'bajingan', 'bangsat', 'goblok', 'kampret', 'kontol', 'memek', 'sialan', 'anjing', 'babi', 'setan',
            'brutal', 'asw', 'bajing', 'bocah', 'bodo', 'cibai', 'cibol', 'cok', 'kont', 'kntl',
            // Tambah lebih banyak dari sumber seperti JSON-mu atau online lists
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Words File Path
    |--------------------------------------------------------------------------
    ...
    */
    'custom_words_file_path' => [
        'en' => [],
        'fr' => [],
        'ar' => [],
        'id' => storage_path('app/bad-words-id.json'),  // Opsional: Auto-import JSON-mu di boot
    ],

    /*
    |--------------------------------------------------------------------------
    | Character Substitutions
    |--------------------------------------------------------------------------
    ...
    */
    'substitutions' => [
        // ... (biarkan seperti asli, atau tambah untuk Indonesian chars jika perlu, e.g., 'à' => ['à', 'a'])
    ],

    /*
    |--------------------------------------------------------------------------
    | Character separators
    |--------------------------------------------------------------------------
    ...
    */
    'separators' => [
        // ... (biarkan seperti asli)
    ],
];