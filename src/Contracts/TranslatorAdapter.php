<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Contracts;

/**
 * Pluggable translation source for enumerator label / description /
 * help / placeholder lookups.
 *
 * Default binding: `Translations\LaravelTranslatorAdapter` (wraps Lang::*).
 *
 * Consumers can swap the binding via `config('enumerator.translator.adapter')`
 * to use a DB-backed source, a third-party translation service, or a
 * test fake.
 */
interface TranslatorAdapter
{
    /**
     * Return the translated string for the given key, or null when the
     * key is not present in the configured source. Returning null lets
     * the caller's fallback chain (attribute → humanized name) run.
     *
     * @param  array<string, scalar|null>  $replace  placeholder substitutions
     */
    public function translate(string $key, array $replace = [], ?string $locale = null): ?string;

    /**
     * Whether the given key is present in the configured source for the
     * given locale (or the current locale when null).
     */
    public function has(string $key, ?string $locale = null): bool;

    /**
     * Switch the active locale.
     */
    public function setLocale(string $locale): void;

    /**
     * Return the active locale.
     */
    public function getLocale(): string;
}
