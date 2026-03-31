<?php

return [
    /*
    |--------------------------------------------------------------------------
    | File Configuration
    |--------------------------------------------------------------------------
    |
    | Configure file upload settings for imports.
    |
    */
    'file' => [
        'max_size' => env('IMPORT_MAX_FILE_SIZE', 10240), // KB
        'disk' => env('IMPORT_DISK', 'public'),
        'directory' => 'imports',
        'accepted_types' => [
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'text/csv',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Categories
    |--------------------------------------------------------------------------
    |
    | Define available import categories. These are used to group and filter
    | imports in the audit resource.
    |
    */
    'categories' => [
        // Add your categories here, e.g.:
        // 'clientes' => 'Clientes',
        // 'produtos' => 'Produtos',
    ],

    /*
    |--------------------------------------------------------------------------
    | Database Tables
    |--------------------------------------------------------------------------
    |
    | Configure the table names used by the package.
    |
    */
    'tables' => [
        'importacoes' => 'importacoes',
        'importacao_detalhes' => 'importacao_detalhes',
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging
    |--------------------------------------------------------------------------
    |
    | Configure logging behavior for imports.
    |
    */
    'logging' => [
        'channel' => env('IMPORT_LOG_CHANNEL', null), // null = default channel
        'log_success' => true,
        'log_errors' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Notifications
    |--------------------------------------------------------------------------
    |
    | Configure notification behavior.
    |
    */
    'notifications' => [
        'show_success' => true,
        'show_errors' => true,
        'persistent' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Resource Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the ImportacaoResource appearance.
    |
    */
    'resource' => [
        'navigation_icon' => 'heroicon-o-arrow-up-tray',
        'navigation_group' => null, // e.g., 'Sistema' or 'Integrações'
        'navigation_sort' => 99,
    ],
];
