<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Ações de Importação
    |--------------------------------------------------------------------------
    */
    'action' => [
        'upload' => 'Carregar Excel',
        'upload_description' => 'Selecione um ficheiro Excel para importar',
        'process' => 'Processar Importação',
        'process_description' => 'Processar todos os registos carregados',
        'view_results' => 'Ver Resultados',
        'view_preview' => 'Ver Pré-visualização',
        'download_template' => 'Descarregar Modelo',
        'clear' => 'Limpar Dados',
    ],

    /*
    |--------------------------------------------------------------------------
    | Diálogos Modais
    |--------------------------------------------------------------------------
    */
    'modal' => [
        'upload_heading' => 'Carregar Ficheiro Excel',
        'upload_description' => 'Selecione um ficheiro Excel (.xlsx, .xls) para importar.',
        'upload_submit' => 'Carregar',
        'confirm_heading' => 'Confirmar Processamento',
        'confirm_description' => 'Está prestes a processar :count registo(s). Esta acção não pode ser revertida. Deseja continuar?',
        'confirm_submit' => 'Processar',
        'confirm_cancel' => 'Cancelar',
        'cancel' => 'Cancelar',
        'no_data' => 'Não foram encontrados dados para processar.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Campos do Formulário
    |--------------------------------------------------------------------------
    */
    'form' => [
        'file' => 'Ficheiro Excel',
        'file_helper' => 'Formatos aceites: .xlsx, .xls',
        'month' => 'Mês',
        'year' => 'Ano',
        'code' => 'Código de Importação',
        'category' => 'Categoria',
    ],

    /*
    |--------------------------------------------------------------------------
    | Notificações
    |--------------------------------------------------------------------------
    */
    'notifications' => [
        'file_loaded' => 'Ficheiro Carregado com Sucesso',
        'file_loaded_body' => ':count linhas carregadas e prontas para processamento.',
        'rows_loaded' => ':count linhas carregadas',
        'import_complete' => 'Importação Concluída',
        'import_complete_body' => 'Total: :total | Sucesso: :success | Falha: :failed',
        'import_summary' => 'Total: :total | Sucesso: :success | Falha: :failed',
        'no_file' => 'Nenhum Ficheiro Selecionado',
        'no_file_body' => 'Por favor, carregue primeiro um ficheiro Excel.',
        'no_data' => 'Sem Dados',
        'no_data_body' => 'Não foram encontrados dados para processar.',
        'invalid_file' => 'Ficheiro Inválido',
        'invalid_file_body' => 'O formato do ficheiro não é suportado. Por favor, utilize ficheiros .xlsx ou .xls.',
        'error' => 'Erro',
        'import_error' => 'Ocorreu um erro durante o processamento da importação.',
        'error_body' => 'Ocorreu um erro durante o processamento: :message',
        'processing' => 'A processar...',
        'processing_body' => 'Por favor, aguarde enquanto os dados são processados.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Tabela
    |--------------------------------------------------------------------------
    */
    'table' => [
        'empty_heading' => 'Nenhum Dado Carregado',
        'empty_description' => 'Utilize o botão "Carregar Excel" para selecionar um ficheiro.',
        'status' => 'Estado',
        'error' => 'Erro',
        'columns' => [
            'row' => 'Linha',
            'status' => 'Estado',
            'error' => 'Erro',
            'payload' => 'Dados',
            'time' => 'Tempo (ms)',
            'created_at' => 'Data',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Secções
    |--------------------------------------------------------------------------
    */
    'section' => [
        'preview' => 'Pré-visualização',
        'results' => 'Resultados',
        'row_count' => ':count linhas',
    ],

    /*
    |--------------------------------------------------------------------------
    | Etiquetas de Estado
    |--------------------------------------------------------------------------
    */
    'status' => [
        'success' => 'Sucesso',
        'failed' => 'Falhou',
        'error' => 'Erro',
        'pending' => 'Pendente',
        'processing' => 'A processar',
        'skipped' => 'Ignorado',
        'duplicate' => 'Duplicado',
    ],

    /*
    |--------------------------------------------------------------------------
    | Resource (Auditoria)
    |--------------------------------------------------------------------------
    */
    'page' => [
        'title' => 'Importação',
    ],

    'errors' => [
        'file_not_found' => 'Ficheiro não encontrado.',
    ],

    'resource' => [
        'label' => 'Importação',
        'plural_label' => 'Importações',
        'navigation_label' => 'Histórico de Importações',
        'navigation_group' => 'Sistema',

        'codigo' => 'Código',
        'categoria' => 'Categoria',
        'total' => 'Total',
        'sucesso' => 'Sucesso',
        'falha' => 'Falha',
        'user' => 'Utilizador',
        'created_at' => 'Criado Em',
        'updated_at' => 'Actualizado Em',
        'details' => 'Detalhes da Importação',
        'success_rate' => 'Taxa de Sucesso',
        'status' => 'Estado',
        'payload' => 'Dados',
        'erro' => 'Erro',
        'tempo' => 'Tempo (ms)',

        'fields' => [
            'codigo' => 'Código',
            'categoria' => 'Categoria',
            'tipo_operacao' => 'Tipo de Operação',
            'total' => 'Total',
            'sucesso' => 'Sucesso',
            'falha' => 'Falha',
            'user' => 'Utilizador',
            'concluido' => 'Concluído',
            'detalhes' => 'Detalhes',
            'created_at' => 'Criado Em',
            'updated_at' => 'Actualizado Em',
        ],

        'tabs' => [
            'all' => 'Todos',
            'today' => 'Hoje',
            'this_week' => 'Esta Semana',
            'this_month' => 'Este Mês',
        ],

        'filters' => [
            'category' => 'Categoria',
            'status' => 'Estado',
            'user' => 'Utilizador',
            'date_from' => 'De',
            'date_to' => 'Até',
        ],

        'actions' => [
            'view' => 'Ver Detalhes',
            'export' => 'Exportar',
            'retry' => 'Retentar Falhados',
            'retry_heading' => 'Retentar Registos Falhados',
            'retry_description' => ':count registo(s) falharam. Será redirecionado para re-carregar e re-processar o ficheiro.',
            'back' => 'Voltar à Lista',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Categorias
    |--------------------------------------------------------------------------
    */
    'categories' => [
        'clientes' => 'Clientes',
        'contadores' => 'Contadores',
        'contratos' => 'Contratos',
        'leituras' => 'Leituras',
        'localizacoes' => 'Localizações',
        'faturas' => 'Faturas',
        'pagamentos' => 'Pagamentos',
        'outros' => 'Outros',
    ],

    /*
    |--------------------------------------------------------------------------
    | Comandos
    |--------------------------------------------------------------------------
    */
    'commands' => [
        'install' => [
            'description' => 'Instalar pacote Advanced Import',
            'publishing_config' => 'A publicar configuração...',
            'publishing_migrations' => 'A publicar migrations...',
            'running_migrations' => 'A executar migrations...',
            'success' => 'Advanced Import instalado com sucesso!',
        ],
        'page' => [
            'description' => 'Criar uma nova página de importação para um resource Filament',
            'success' => 'Página de importação criada com sucesso!',
            'already_exists' => 'A página de importação já existe!',
        ],
        'processor' => [
            'description' => 'Criar um novo processador de importação',
            'success' => 'Processador de importação criado com sucesso!',
            'already_exists' => 'O processador de importação já existe!',
        ],
        'publish' => [
            'description' => 'Publicar assets do Advanced Import',
            'success' => 'Assets publicados com sucesso!',
        ],
    ],
];
