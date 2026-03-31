<?php

namespace Filament\AdvancedImport\Concerns;

use Filament\Actions\Action;
use Filament\AdvancedImport\Contracts\ImportProcessor;
use Filament\AdvancedImport\Support\ImportConfig;
use Filament\Forms\Components\FileUpload;

trait HasImportActions
{
    /**
     * Get all header actions for the import page.
     */
    protected function getHeaderActions(): array
    {
        return array_filter([
            $this->getDownloadTemplateAction(),
            $this->getUploadAction(),
            $this->getProcessAction(),
            $this->getViewResultsAction(),
            $this->getViewPreviewAction(),
        ]);
    }

    /**
     * Get the download template action.
     * Override this method to provide a template download.
     */
    protected function getDownloadTemplateAction(): ?Action
    {
        return null;
    }

    /**
     * Get the upload action.
     */
    protected function getUploadAction(): Action
    {
        return Action::make('uploadExcel')
            ->label(__('advanced-import::messages.action.upload'))
            ->icon('heroicon-o-arrow-up-tray')
            ->modalHeading(__('advanced-import::messages.modal.upload_heading'))
            ->modalSubmitActionLabel(__('advanced-import::messages.modal.upload_submit'))
            ->modalCancelActionLabel(__('advanced-import::messages.modal.cancel'))
            ->form($this->getUploadFormSchema())
            ->action(function (array $data): void {
                $this->handleUpload($data);
            });
    }

    /**
     * Get the process action.
     */
    protected function getProcessAction(): Action
    {
        return Action::make('processarImportacao')
            ->label(__('advanced-import::messages.action.process'))
            ->icon('heroicon-o-check-circle')
            ->color('success')
            ->requiresConfirmation()
            ->modalHeading(__('advanced-import::messages.modal.confirm_heading'))
            ->modalDescription(function (): string {
                $count = count($this->preview);

                if (empty($this->preview)) {
                    return __('advanced-import::messages.modal.no_data');
                }

                return __('advanced-import::messages.modal.confirm_description', ['count' => $count]);
            })
            ->disabled(fn (): bool => empty($this->preview))
            ->action(function (): void {
                $this->handleProcess();
            });
    }

    /**
     * Get the view results action.
     */
    protected function getViewResultsAction(): Action
    {
        return Action::make('verResultado')
            ->label(__('advanced-import::messages.action.view_results'))
            ->icon('heroicon-o-table-cells')
            ->color('info')
            ->hidden(fn (): bool => empty($this->resultado) || $this->mostrarResultado)
            ->action(fn () => $this->mostrarResultado = true);
    }

    /**
     * Get the view preview action.
     */
    protected function getViewPreviewAction(): Action
    {
        return Action::make('verPreview')
            ->label(__('advanced-import::messages.action.view_preview'))
            ->icon('heroicon-o-eye')
            ->color('gray')
            ->hidden(fn (): bool => ! $this->mostrarResultado)
            ->action(fn () => $this->mostrarResultado = false);
    }

    /**
     * Get the upload form schema.
     * Override this method to add additional fields.
     */
    protected function getUploadFormSchema(): array
    {
        return [
            FileUpload::make('file')
                ->label(__('advanced-import::messages.form.file'))
                ->required()
                ->disk(ImportConfig::getDisk())
                ->directory($this->getUploadDirectory())
                ->maxSize(ImportConfig::getMaxFileSize())
                ->acceptedFileTypes(ImportConfig::getAcceptedTypes()),
        ];
    }

    /**
     * Handle the upload action.
     */
    protected function handleUpload(array $data): void
    {
        $path = $data['file'] ?? null;

        if (! $path) {
            $this->notifyNoFile();

            return;
        }

        try {
            $this->preview = $this->readExcelFile($path);
            $this->resultado = [];
            $this->mostrarResultado = false;

            // Store additional context from form data
            $this->storeUploadContext($data);

            $this->notifyFileLoaded(count($this->preview));

        } catch (\Throwable $e) {
            $this->notifyInvalidFile($e->getMessage());
        }
    }

    /**
     * Store additional context from upload form.
     * Override this method to store custom form data.
     */
    protected function storeUploadContext(array $data): void
    {
        // Override in implementing class if needed
    }

    /**
     * Handle the process action.
     */
    protected function handleProcess(): void
    {
        try {
            $processor = $this->getImportProcessor();
            $context = $this->getProcessContext();

            $result = $processor->process($this->preview, $context);

            $this->resultado = $result['dados'] ?? [];
            $this->mostrarResultado = true;

            $this->notifyImportComplete(
                $result['total'] ?? 0,
                $result['sucesso'] ?? 0,
                $result['falha'] ?? 0
            );

        } catch (\Throwable $e) {
            $this->notifyImportError($e->getMessage());
        }
    }

    /**
     * Get the context for processing.
     * Override this method to provide custom context.
     */
    protected function getProcessContext(): array
    {
        return [];
    }

    /**
     * Get the import processor.
     * This method must be implemented by the page class.
     */
    abstract protected function getImportProcessor(): ImportProcessor;
}
