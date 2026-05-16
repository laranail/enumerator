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
            // When an explicit locale is given, suppress the configured
            // locale-fallback chain — the caller (e.g. IsTranslatable)
            // needs `null` here to trigger its own #[Label] / humanize
            // fallback. Allow Laravel's fallback only when the caller
            // hasn't asked for a specific locale.
            $translated = (string) Lang::get($key, $replace, $locale, $locale === null);
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
            // Same fallback discipline as translate(): an explicit locale
            // means "only check this locale, no app-wide fallback".
            return Lang::has($key, $locale, $locale === null);
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
