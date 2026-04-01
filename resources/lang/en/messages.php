<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Import Actions
    |--------------------------------------------------------------------------
    */
    'action' => [
        'upload' => 'Upload Excel',
        'upload_description' => 'Select an Excel file to import',
        'process' => 'Process Import',
        'process_description' => 'Process all loaded records',
        'view_results' => 'View Results',
        'view_preview' => 'View Preview',
        'download_template' => 'Download Template',
        'clear' => 'Clear Data',
    ],

    /*
    |--------------------------------------------------------------------------
    | Modal Dialogs
    |--------------------------------------------------------------------------
    */
    'modal' => [
        'upload_heading' => 'Upload Excel File',
        'upload_description' => 'Select an Excel file (.xlsx, .xls) to import.',
        'upload_submit' => 'Upload',
        'confirm_heading' => 'Confirm Processing',
        'confirm_description' => 'You are about to process :count record(s). This action cannot be undone. Do you want to continue?',
        'confirm_submit' => 'Process',
        'confirm_cancel' => 'Cancel',
        'cancel' => 'Cancel',
        'no_data' => 'No data found to process.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Form Fields
    |--------------------------------------------------------------------------
    */
    'form' => [
        'file' => 'Excel File',
        'file_helper' => 'Accepted formats: .xlsx, .xls',
        'month' => 'Month',
        'year' => 'Year',
        'code' => 'Import Code',
        'category' => 'Category',
    ],

    /*
    |--------------------------------------------------------------------------
    | Notifications
    |--------------------------------------------------------------------------
    */
    'notifications' => [
        'file_loaded' => 'File Loaded Successfully',
        'file_loaded_body' => ':count rows loaded and ready for processing.',
        'rows_loaded' => ':count rows loaded',
        'import_complete' => 'Import Complete',
        'import_complete_body' => 'Total: :total | Success: :success | Failed: :failed',
        'import_summary' => 'Total: :total | Success: :success | Failed: :failed',
        'no_file' => 'No File Selected',
        'no_file_body' => 'Please upload an Excel file first.',
        'no_data' => 'No Data',
        'no_data_body' => 'No data found to process.',
        'invalid_file' => 'Invalid File',
        'invalid_file_body' => 'The file format is not supported. Please use .xlsx or .xls files.',
        'error' => 'Error',
        'import_error' => 'An error occurred while processing the import.',
        'error_body' => 'An error occurred while processing: :message',
        'processing' => 'Processing...',
        'processing_body' => 'Please wait while the data is being processed.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Table
    |--------------------------------------------------------------------------
    */
    'table' => [
        'empty_heading' => 'No Data Loaded',
        'empty_description' => 'Use the "Upload Excel" button to select a file.',
        'status' => 'Status',
        'error' => 'Error',
        'columns' => [
            'row' => 'Row',
            'status' => 'Status',
            'error' => 'Error',
            'payload' => 'Data',
            'time' => 'Time (ms)',
            'created_at' => 'Date',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Section Labels
    |--------------------------------------------------------------------------
    */
    'section' => [
        'preview' => 'Preview',
        'results' => 'Results',
        'row_count' => ':count rows',
    ],

    /*
    |--------------------------------------------------------------------------
    | Status Labels
    |--------------------------------------------------------------------------
    */
    'status' => [
        'success' => 'Success',
        'failed' => 'Failed',
        'error' => 'Error',
        'pending' => 'Pending',
        'processing' => 'Processing',
        'skipped' => 'Skipped',
        'duplicate' => 'Duplicate',
    ],

    /*
    |--------------------------------------------------------------------------
    | Resource (Audit)
    |--------------------------------------------------------------------------
    */
    'page' => [
        'title' => 'Import',
    ],

    'errors' => [
        'file_not_found' => 'File not found.',
    ],

    'resource' => [
        'label' => 'Import',
        'plural_label' => 'Imports',
        'navigation_label' => 'Import History',
        'navigation_group' => 'System',

        'codigo' => 'Code',
        'categoria' => 'Category',
        'total' => 'Total',
        'sucesso' => 'Success',
        'falha' => 'Failed',
        'user' => 'User',
        'created_at' => 'Created At',
        'updated_at' => 'Updated At',
        'details' => 'Import Details',
        'success_rate' => 'Success Rate',
        'status' => 'Status',
        'payload' => 'Data',
        'erro' => 'Error',
        'tempo' => 'Time (ms)',

        'fields' => [
            'codigo' => 'Code',
            'categoria' => 'Category',
            'tipo_operacao' => 'Operation Type',
            'total' => 'Total',
            'sucesso' => 'Success',
            'falha' => 'Failed',
            'user' => 'User',
            'concluido' => 'Completed',
            'detalhes' => 'Details',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ],

        'tabs' => [
            'all' => 'All',
            'today' => 'Today',
            'this_week' => 'This Week',
            'this_month' => 'This Month',
        ],

        'filters' => [
            'category' => 'Category',
            'status' => 'Status',
            'user' => 'User',
            'date_from' => 'From',
            'date_to' => 'To',
        ],

        'actions' => [
            'view' => 'View Details',
            'export' => 'Export',
            'retry' => 'Retry Failed',
            'retry_heading' => 'Retry Failed Records',
            'retry_description' => ':count record(s) failed. You will be redirected to re-upload and re-process the file.',
            'back' => 'Back to List',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Categories
    |--------------------------------------------------------------------------
    */
    'categories' => [
        'clientes' => 'Clients',
        'contadores' => 'Meters',
        'contratos' => 'Contracts',
        'leituras' => 'Readings',
        'localizacoes' => 'Locations',
        'faturas' => 'Invoices',
        'pagamentos' => 'Payments',
        'outros' => 'Others',
    ],

    /*
    |--------------------------------------------------------------------------
    | Commands
    |--------------------------------------------------------------------------
    */
    'commands' => [
        'install' => [
            'description' => 'Install Advanced Import package',
            'publishing_config' => 'Publishing configuration...',
            'publishing_migrations' => 'Publishing migrations...',
            'running_migrations' => 'Running migrations...',
            'success' => 'Advanced Import installed successfully!',
        ],
        'page' => [
            'description' => 'Create a new import page for a Filament resource',
            'success' => 'Import page created successfully!',
            'already_exists' => 'Import page already exists!',
        ],
        'processor' => [
            'description' => 'Create a new import processor',
            'success' => 'Import processor created successfully!',
            'already_exists' => 'Import processor already exists!',
        ],
        'publish' => [
            'description' => 'Publish Advanced Import assets',
            'success' => 'Assets published successfully!',
        ],
    ],
];
