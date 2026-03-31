<?php

namespace Filament\AdvancedImport\Support;

class ImportConfig
{
    /**
     * Get the maximum file size in KB.
     */
    public static function getMaxFileSize(): int
    {
        return (int) config('advanced-import.file.max_size', 10240);
    }

    /**
     * Get the storage disk.
     */
    public static function getDisk(): string
    {
        return config('advanced-import.file.disk', 'public');
    }

    /**
     * Get the upload directory.
     */
    public static function getDirectory(): string
    {
        return config('advanced-import.file.directory', 'imports');
    }

    /**
     * Get accepted file types.
     */
    public static function getAcceptedTypes(): array
    {
        return config('advanced-import.file.accepted_types', [
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * Get available categories.
     */
    public static function getCategories(): array
    {
        return config('advanced-import.categories', []);
    }

    /**
     * Get category label.
     */
    public static function getCategoryLabel(string $category): string
    {
        $categories = static::getCategories();

        return $categories[$category] ?? ucfirst($category);
    }

    /**
     * Check if notifications should show success.
     */
    public static function shouldShowSuccess(): bool
    {
        return config('advanced-import.notifications.show_success', true);
    }

    /**
     * Check if notifications should show errors.
     */
    public static function shouldShowErrors(): bool
    {
        return config('advanced-import.notifications.show_errors', true);
    }

    /**
     * Check if notifications should be persistent.
     */
    public static function shouldBePersistent(): bool
    {
        return config('advanced-import.notifications.persistent', true);
    }

    /**
     * Get the log channel.
     */
    public static function getLogChannel(): ?string
    {
        return config('advanced-import.logging.channel');
    }

    /**
     * Get resource navigation icon.
     */
    public static function getNavigationIcon(): string
    {
        return config('advanced-import.resource.navigation_icon', 'heroicon-o-arrow-up-tray');
    }

    /**
     * Get resource navigation group.
     */
    public static function getNavigationGroup(): ?string
    {
        return config('advanced-import.resource.navigation_group');
    }

    /**
     * Get resource navigation sort.
     */
    public static function getNavigationSort(): int
    {
        return (int) config('advanced-import.resource.navigation_sort', 99);
    }
}
