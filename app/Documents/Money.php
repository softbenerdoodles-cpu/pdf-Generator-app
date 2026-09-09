<?php

namespace App\Documents;

/**
 * Integer-cent arithmetic and formatting.
 *
 * Totals are summed in cents so repeated addition of values like 0.1 cannot
 * drift the way binary floats do.
 */
class Money
{
    protected const SYMBOLS = [
        'USD' => '$',
        'EUR' => '&euro;',
        'GBP' => '&pound;',
        'CAD' => 'CA$',
        'AUD' => 'A$',
        'JPY' => '&yen;',
    ];

    public static function currencies(): array
    {
        return [
            'USD' => 'USD — US dollar',
            'EUR' => 'EUR — Euro',
            'GBP' => 'GBP — Pound sterling',
            'CAD' => 'CAD — Canadian dollar',
            'AUD' => 'AUD — Australian dollar',
            'JPY' => 'JPY — Japanese yen',
        ];
    }

    public static function toCents(mixed $amount): int
    {
        return (int) round(((float) $amount) * 100);
    }

    /** Returns an HTML-safe amount; the symbol may be an entity. */
    public static function format(int $cents, string $currency = 'USD'): string
    {
        $symbol = self::SYMBOLS[strtoupper($currency)] ?? (strtoupper($currency).'&nbsp;');
        $sign = $cents < 0 ? '-' : '';

        return $sign.$symbol.number_format(abs($cents) / 100, 2);
    }
}
