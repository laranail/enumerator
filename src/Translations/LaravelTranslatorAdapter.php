<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Translations;

use Illuminate\Support\Facades\Lang;
use Simtabi\Laranail\Enumerator\Contracts\TranslatorAdapter;

/**
 * Default translation adapter — wraps Laravel's `Lang::*` facade.
 *
 * Preserves the Laravel translator behaviour when no adapter override is
 * configured. Defensive against contexts where Lang isn't bound
 * (raw unit tests of traits in isolation).
 */
final class LaravelTranslatorAdapter implements TranslatorAdapter
{
    public function translate(string $key, array $replace = [], ?string $locale = null): ?string
    {
        if (! $this->has($key, $locale)) {
            return null;
        }

        try {
            $translated = (string) Lang::get($key, $replace, $locale);
        } catch (\Throwable) {
            return null;
        }

        // Lang::get returns the key itself when no translation exists.
        // Treat that as a miss so the caller's fallback chain runs.
        return $translated === '' || $translated === $key ? null : $translated;
    }

    public function has(string $key, ?string $locale = null): bool
    {
        if (! class_exists(Lang::class, false) && ! function_exists('trans')) {
            return false;
        }

        try {
            return Lang::has($key, $locale);
        } catch (\Throwable) {
            return false;
        }
    }

    public function setLocale(string $locale): void
    {
        if (function_exists('app')) {
            app()->setLocale($locale);
        }
    }

    public function getLocale(): string
    {
        if (function_exists('app')) {
            return (string) app()->getLocale();
        }

        return 'en';
    }
}
