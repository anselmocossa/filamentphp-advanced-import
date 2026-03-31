<?php

namespace Filament\AdvancedImport\Concerns;

use Filament\AdvancedImport\Support\ExcelReader;
use Filament\AdvancedImport\Support\ImportConfig;
use Illuminate\Support\Facades\Storage;

trait HasExcelParsing
{
    /**
     * Read and parse an Excel file.
     *
     * @param  string  $path  Path to the file (relative to disk)
     * @return array  Array of parsed rows
     *
     * @throws \Exception
     */
    protected function readExcelFile(string $path): array
    {
        $disk = $this->getUploadDisk();
        $absolutePath = Storage::disk($disk)->path($path);

        $rows = ExcelReader::readFromPath($absolutePath);

        return array_map(fn ($row) => $this->parseRow($row), $rows);
    }

    /**
     * Get the upload disk.
     */
    protected function getUploadDisk(): string
    {
        return ImportConfig::getDisk();
    }

    /**
     * Get the upload directory.
     */
    protected function getUploadDirectory(): string
    {
        return ImportConfig::getDirectory();
    }

    /**
     * Get the max file size in KB.
     */
    protected function getMaxFileSize(): int
    {
        return ImportConfig::getMaxFileSize();
    }

    /**
     * Get accepted file types.
     */
    protected function getAcceptedFileTypes(): array
    {
        return ImportConfig::getAcceptedTypes();
    }

    /**
     * Parse a single row from Excel.
     * This method should be overridden in the implementing class.
     *
     * @param  array  $row  Raw row data from Excel
     * @return array  Parsed row data
     */
    abstract protected function parseRow(array $row): array;

    /**
     * Get a string value from a row.
     */
    protected function getString(array $row, string $key, string $default = ''): string
    {
        return trim((string) ($row[$key] ?? $default));
    }

    /**
     * Get an integer value from a row.
     */
    protected function getInt(array $row, string $key, int $default = 0): int
    {
        $value = $row[$key] ?? $default;

        return is_numeric($value) ? (int) $value : $default;
    }

    /**
     * Get a float value from a row.
     */
    protected function getFloat(array $row, string $key, float $default = 0.0): float
    {
        $value = $row[$key] ?? $default;

        if (is_numeric($value)) {
            return (float) $value;
        }

        // Handle comma as decimal separator
        $value = str_replace(',', '.', (string) $value);
        $value = preg_replace('/[^0-9.\-]/', '', $value);

        return is_numeric($value) ? (float) $value : $default;
    }

    /**
     * Get a boolean value from a row.
     */
    protected function getBool(array $row, string $key, bool $default = false): bool
    {
        $value = $row[$key] ?? null;

        if ($value === null) {
            return $default;
        }

        if (is_bool($value)) {
            return $value;
        }

        $truthy = ['1', 'true', 'yes', 'sim', 's', 'y', 'verdadeiro'];

        return in_array(strtolower(trim((string) $value)), $truthy, true);
    }

    /**
     * Get a date value from a row.
     */
    protected function getDate(array $row, string $key, ?string $format = null): ?\Carbon\Carbon
    {
        $value = $row[$key] ?? null;

        if (empty($value)) {
            return null;
        }

        try {
            if ($format) {
                return \Carbon\Carbon::createFromFormat($format, $value);
            }

            return \Carbon\Carbon::parse($value);
        } catch (\Exception) {
            return null;
        }
    }
}
