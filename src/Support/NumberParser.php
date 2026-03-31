<?php

namespace Filament\AdvancedImport\Support;

class NumberParser
{
    /**
     * Parse a numeric value from various formats.
     *
     * Handles:
     * - Comma as decimal separator (150,5 → 150.5)
     * - Spaces as thousand separators (1 500,5 → 1500.5)
     * - Non-breaking spaces (NBSP)
     * - Various currency symbols
     */
    public static function parse(mixed $value): float
    {
        if (is_numeric($value)) {
            return (float) $value;
        }

        $v = (string) ($value ?? '');

        // Remove whitespace (including NBSP)
        $v = preg_replace('/\s+/u', '', $v);
        $v = str_replace("\xC2\xA0", '', $v);

        // Replace comma with dot for decimal separator
        $v = str_replace(',', '.', $v);

        // Remove everything except numbers, dots, and minus sign
        $v = preg_replace('/[^0-9.\-]/', '', $v);

        // Handle multiple dots (keep only the last one as decimal)
        if (substr_count($v, '.') > 1) {
            $parts = explode('.', $v);
            $decimal = array_pop($parts);
            $v = implode('', $parts).'.'.$decimal;
        }

        return is_numeric($v) ? (float) $v : 0.0;
    }

    /**
     * Parse an integer value.
     */
    public static function parseInt(mixed $value): int
    {
        return (int) static::parse($value);
    }

    /**
     * Parse a value with a specific number of decimal places.
     */
    public static function parseDecimal(mixed $value, int $decimals = 2): float
    {
        return round(static::parse($value), $decimals);
    }

    /**
     * Check if a value is numeric (after parsing).
     */
    public static function isNumeric(mixed $value): bool
    {
        if (is_numeric($value)) {
            return true;
        }

        $v = (string) ($value ?? '');
        $v = preg_replace('/\s+/u', '', $v);
        $v = str_replace(',', '.', $v);
        $v = preg_replace('/[^0-9.\-]/', '', $v);

        return is_numeric($v) && $v !== '';
    }
}
