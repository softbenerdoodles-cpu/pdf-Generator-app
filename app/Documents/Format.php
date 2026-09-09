<?php

namespace App\Documents;

use Carbon\CarbonImmutable;
use Illuminate\Support\HtmlString;

/**
 * Small formatting helpers used by the PDF views.
 */
class Format
{
    /** "2026-09-30" becomes "30 September 2026"; anything unparseable is left alone. */
    public static function date(mixed $value, string $format = 'j F Y'): string
    {
        if (blank($value)) {
            return '';
        }

        try {
            return CarbonImmutable::parse((string) $value)->format($format);
        } catch (\Throwable) {
            return (string) $value;
        }
    }

    /** Escape user text and keep its line breaks. */
    public static function lines(mixed $value): HtmlString
    {
        return new HtmlString(nl2br(e((string) $value), false));
    }

    /** Trim a number like 1.0 to "1" but leave 12.5 alone. */
    public static function quantity(mixed $value): string
    {
        $number = (float) $value;

        return rtrim(rtrim(number_format($number, 2, '.', ''), '0'), '.') ?: '0';
    }
}
