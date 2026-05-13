<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Helpers;

use Illuminate\Support\Str;

/**
 * String formatting helpers shared across the package. Pure functions; safe to
 * call from concerns without caching.
 */
final class Humanizer
{
    /**
     * Convert a case/constant name into a Title-Cased human label.
     *
     * Examples:
     *   "ACTIVE"           → "Active"
     *   "IN_PROGRESS"      → "In Progress"
     *   "NeedsRevision"    => "Needs Revision"
     *   "needs-revision"   => "Needs Revision"
     */
    public static function humanize(string $value): string
    {
        // All-uppercase constant-style names: lowercase before title-casing.
        if (preg_match('/^[A-Z][A-Z0-9_-]*$/', $value)) {
            $value = Str::lower($value);
        }
        // Acronym-PascalCase: e.g. HTTPMethod → "HTTP Method",
        // XMLHttpRequest → "XML Http Request". Insert a space before each
        // capital that immediately precedes a lowercase letter or follows
        // a lowercase letter.
        elseif (preg_match('/^[A-Z]+[A-Z][a-z]+/', $value)) {
            $value = (string) preg_replace('/([A-Z]+)([A-Z][a-z])/', '$1 $2', $value);
            $value = (string) preg_replace('/([a-z])([A-Z])/', '$1 $2', $value);
        }
        // PascalCase or camelCase → insert spaces before internal capitals.
        elseif (preg_match('/^[A-Za-z][a-z]+(?:[A-Z][a-z]*)+$/', $value)) {
            $value = (string) preg_replace('/(?<!^)(?=[A-Z])/', ' ', $value);
        }

        $value = str_replace(['-', '_'], ' ', $value);

        return trim((string) Str::of($value)->title());
    }

    /**
     * Convert a class basename into a snake-cased slug, dropping trailing
     * "Enum". Used to derive a translation slug for an enum class.
     *
     * Examples:
     *   "UserStatusEnum"          => "user_status"
     *   "OrderStatusEnum"         => "order_status"
     *   "HTTPMethod"              => "http_method"
     */
    public static function slugify(string $classBasename): string
    {
        $base = preg_replace('/Enum$/', '', $classBasename) ?? $classBasename;

        return (string) Str::of($base)->snake();
    }
}
