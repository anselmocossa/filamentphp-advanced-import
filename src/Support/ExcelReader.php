<?php

namespace Filament\AdvancedImport\Support;

use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Facades\Excel;

class ExcelReader
{
    /**
     * Read an Excel file and return its contents as an array.
     *
     * @param  string  $path  Path to the file (relative to disk)
     * @param  string|null  $disk  Storage disk name
     * @return array  Array of rows from the first sheet
     *
     * @throws \Exception
     */
    public static function read(string $path, ?string $disk = null): array
    {
        $disk = $disk ?? ImportConfig::getDisk();
        $absolutePath = Storage::disk($disk)->path($path);

        if (! file_exists($absolutePath)) {
            throw new \Exception(__('advanced-import::messages.errors.file_not_found'));
        }

        $sheets = Excel::toArray(new class implements ToArray, WithHeadingRow
        {
            public function array(array $array): array
            {
                return $array;
            }
        }, $absolutePath);

        return $sheets[0] ?? [];
    }

    /**
     * Read an Excel file from absolute path.
     *
     * @param  string  $absolutePath  Absolute path to the file
     * @return array  Array of rows from the first sheet
     *
     * @throws \Exception
     */
    public static function readFromPath(string $absolutePath): array
    {
        if (! file_exists($absolutePath)) {
            throw new \Exception(__('advanced-import::messages.errors.file_not_found'));
        }

        $sheets = Excel::toArray(new class implements ToArray, WithHeadingRow
        {
            public function array(array $array): array
            {
                return $array;
            }
        }, $absolutePath);

        return $sheets[0] ?? [];
    }

    /**
     * Read all sheets from an Excel file.
     *
     * @param  string  $path  Path to the file (relative to disk)
     * @param  string|null  $disk  Storage disk name
     * @return array  Array of sheets, each containing array of rows
     */
    public static function readAllSheets(string $path, ?string $disk = null): array
    {
        $disk = $disk ?? ImportConfig::getDisk();
        $absolutePath = Storage::disk($disk)->path($path);

        if (! file_exists($absolutePath)) {
            throw new \Exception(__('advanced-import::messages.errors.file_not_found'));
        }

        return Excel::toArray(new class implements ToArray, WithHeadingRow
        {
            public function array(array $array): array
            {
                return $array;
            }
        }, $absolutePath);
    }

    /**
     * Get headers from an Excel file.
     *
     * @param  string  $path  Path to the file (relative to disk)
     * @param  string|null  $disk  Storage disk name
     * @return array  Array of header names
     */
    public static function getHeaders(string $path, ?string $disk = null): array
    {
        $rows = static::read($path, $disk);

        if (empty($rows)) {
            return [];
        }

        return array_keys($rows[0] ?? []);
    }

    /**
     * Count rows in an Excel file.
     *
     * @param  string  $path  Path to the file (relative to disk)
     * @param  string|null  $disk  Storage disk name
     * @return int  Number of data rows (excluding header)
     */
    public static function countRows(string $path, ?string $disk = null): int
    {
        return count(static::read($path, $disk));
    }
}
