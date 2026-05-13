<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Translations;

use Illuminate\Support\Facades\DB;
use Simtabi\Laranail\Enumerator\Contracts\TranslatorAdapter;

/**
 * Reference DB-backed translator adapter. Consumers can use this
 * directly or model their own implementation on it.
 *
 * Expects a table with at least these columns:
 *
 *   - `key`     string  the translation key (e.g. `enumerator::enums.user_status.active.label`)
 *   - `locale`  string  the locale code (e.g. `en`, `de_DE`)
 *   - `value`   string  the translated string
 *
 * Table name and column names are constructor-configurable.
 *
 * Per-process query cache keys lookups on `key|locale` to avoid hitting
 * the database for every case render.
 */
final class DatabaseTranslatorAdapter implements TranslatorAdapter
{
    /** @var array<string, string|false> */
    private array $cache = [];

    private ?string $locale = null;

    /**
     * @param  string  $table  table holding translations
     * @param  string  $keyColumn  column storing the translation key
     * @param  string  $localeColumn  column storing the locale code
     * @param  string  $valueColumn  column storing the translated string
     */
    public function __construct(
        private readonly string $table = 'enum_translations',
        private readonly string $keyColumn = 'key',
        private readonly string $localeColumn = 'locale',
        private readonly string $valueColumn = 'value',
    ) {}

    public function translate(string $key, array $replace = [], ?string $locale = null): ?string
    {
        $row = $this->lookup($key, $locale ?? $this->getLocale());
        if ($row === null) {
            return null;
        }

        return $this->applyReplacements($row, $replace);
    }

    public function has(string $key, ?string $locale = null): bool
    {
        return $this->lookup($key, $locale ?? $this->getLocale()) !== null;
    }

    public function setLocale(string $locale): void
    {
        $this->locale = $locale;
    }

    public function getLocale(): string
    {
        if ($this->locale !== null) {
            return $this->locale;
        }
        if (function_exists('app')) {
            return (string) app()->getLocale();
        }

        return 'en';
    }

    /**
     * Clear the per-process lookup cache. Useful in tests.
     */
    public function flush(): void
    {
        $this->cache = [];
    }

    private function lookup(string $key, string $locale): ?string
    {
        $cacheKey = $key . '|' . $locale;
        if (array_key_exists($cacheKey, $this->cache)) {
            $cached = $this->cache[$cacheKey];

            return $cached === false ? null : $cached;
        }

        try {
            $value = DB::table($this->table)
                ->where($this->keyColumn, $key)
                ->where($this->localeColumn, $locale)
                ->value($this->valueColumn);
        } catch (\Throwable) {
            // DB not configured / table missing — graceful fallthrough.
            $this->cache[$cacheKey] = false;

            return null;
        }

        if ($value === null || $value === '') {
            $this->cache[$cacheKey] = false;

            return null;
        }

        $string = (string) $value;
        $this->cache[$cacheKey] = $string;

        return $string;
    }

    /**
     * @param  array<string, scalar|null>  $replace
     */
    private function applyReplacements(string $value, array $replace): string
    {
        if ($replace === []) {
            return $value;
        }
        $search = [];
        $replacements = [];
        foreach ($replace as $key => $val) {
            $search[] = ':' . $key;
            $replacements[] = (string) ($val ?? '');
        }

        return str_replace($search, $replacements, $value);
    }
}
