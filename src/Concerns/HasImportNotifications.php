<?php

namespace Filament\AdvancedImport\Concerns;

use Filament\AdvancedImport\Support\ImportConfig;
use Filament\Notifications\Notification;

trait HasImportNotifications
{
    /**
     * Send a success notification.
     */
    protected function notifySuccess(string $title, ?string $body = null): void
    {
        if (! ImportConfig::shouldShowSuccess()) {
            return;
        }

        $notification = Notification::make()
            ->title($title)
            ->success();

        if ($body) {
            $notification->body($body);
        }

        if (ImportConfig::shouldBePersistent()) {
            $notification->persistent();
        }

        $notification->send();
    }

    /**
     * Send an error notification.
     */
    protected function notifyError(string $title, ?string $body = null): void
    {
        if (! ImportConfig::shouldShowErrors()) {
            return;
        }

        $notification = Notification::make()
            ->title($title)
            ->danger();

        if ($body) {
            $notification->body($body);
        }

        if (ImportConfig::shouldBePersistent()) {
            $notification->persistent();
        }

        $notification->send();
    }

    /**
     * Send a warning notification.
     */
    protected function notifyWarning(string $title, ?string $body = null): void
    {
        $notification = Notification::make()
            ->title($title)
            ->warning();

        if ($body) {
            $notification->body($body);
        }

        $notification->send();
    }

    /**
     * Send an info notification.
     */
    protected function notifyInfo(string $title, ?string $body = null): void
    {
        $notification = Notification::make()
            ->title($title)
            ->info();

        if ($body) {
            $notification->body($body);
        }

        $notification->send();
    }

    /**
     * Notify file loaded successfully.
     */
    protected function notifyFileLoaded(int $rowCount): void
    {
        $this->notifySuccess(
            __('advanced-import::messages.notifications.file_loaded'),
            __('advanced-import::messages.notifications.rows_loaded', ['count' => $rowCount])
        );
    }

    /**
     * Notify import completed.
     */
    protected function notifyImportComplete(int $total, int $success, int $failed): void
    {
        $this->notifySuccess(
            __('advanced-import::messages.notifications.import_complete'),
            __('advanced-import::messages.notifications.import_summary', [
                'total' => $total,
                'success' => $success,
                'failed' => $failed,
            ])
        );
    }

    /**
     * Notify no file selected.
     */
    protected function notifyNoFile(): void
    {
        $this->notifyError(__('advanced-import::messages.notifications.no_file'));
    }

    /**
     * Notify invalid file.
     */
    protected function notifyInvalidFile(?string $message = null): void
    {
        $this->notifyError(
            __('advanced-import::messages.notifications.invalid_file'),
            $message
        );
    }

    /**
     * Notify import error.
     */
    protected function notifyImportError(string $message): void
    {
        $this->notifyError(
            __('advanced-import::messages.notifications.import_error'),
            $message
        );
    }
}
