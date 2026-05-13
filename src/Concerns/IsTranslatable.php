<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Concerns;

use BackedEnum;
use Illuminate\Support\Facades\Lang;
use Simtabi\Laranail\Enumerator\Contracts\TranslatorAdapter;
use Simtabi\Laranail\Enumerator\Helpers\Humanizer;
use UnitEnum;

/**
 * Translation lookup for enum cases.
 *
 * Resolution order for `->label($locale = null)`:
 *   1. Translation key `{namespace}::enums.{slug}.{case}` → `Lang::has()`
 *   2. `#[Label]` attribute on the case
 *   3. Humanised case name
 *
 * `description()`, `help()`, and `placeholder()` follow the same pattern using
 * the matching sub-key (`.description`, `.help`, `.placeholder`) and the
 * corresponding attribute.
 *
 * Enums implementing Contracts\Translatable can override the namespace
 * (via translationNamespace()) and slug (via translationSlug()). Defaults
 * are `config('enumerator.translation_namespace')` and a snake-cased class
 * basename minus a trailing "Enum".
 */
trait IsTranslatable
{
    public function label(?string $locale = null): string
    {
        return $this->translateField('label', $locale)
            ?? $this->resolvedAttribute('label')
            ?? Humanizer::humanize($this->caseName());
    }

    public function description(?string $locale = null): ?string
    {
        return $this->translateField('description', $locale)
            ?? $this->resolvedAttribute('description');
    }

    public function help(?string $locale = null): ?string
    {
        return $this->translateField('help', $locale)
            ?? $this->resolvedAttribute('help');
    }

    public function placeholder(?string $locale = null): ?string
    {
        return $this->translateField('placeholder', $locale);
    }

    /**
     * Translation namespace (`enumerator` by default). Override per-enum
     * by implementing Contracts\Translatable.
     */
    public static function translationNamespace(): string
    {
        if (function_exists('config')) {
            return (string) (config('enumerator.translation_namespace') ?? 'enumerator');
        }

        return 'enumerator';
    }

    /**
     * Slug used in the translation key path. Defaults to snake-case of the
     * class basename minus a trailing "Enum".
     */
    public static function translationSlug(): string
    {
        return Humanizer::slugify(class_basename(static::class));
    }

    /**
     * Build a translation key for a sub-field (label, description, help,
     * placeholder, or any custom suffix).
     */
    public static function translationKey(string $case, ?string $field = null): string
    {
        $base = sprintf('%s::enums.%s.%s', static::translationNamespace(), static::translationSlug(), $case);

        return $field === null ? $base : $base . '.' . $field;
    }

    private function translateField(string $field, ?string $locale): ?string
    {
        // Resolve through the bound TranslatorAdapter; consumers can
        // swap the binding via config('enumerator.translator.adapter').
        $adapter = $this->resolveTranslatorAdapter();
        if ($adapter === null) {
            return null;
        }

        $case = $this->caseKey();

        // Field-specific key: namespace::enums.slug.case.field
        $deep = static::translationKey($case, $field);
        $translated = $adapter->translate($deep, [], $locale);
        if ($translated !== null) {
            return $translated;
        }

        if ($field === 'label') {
            // Flat key fallback: namespace::enums.slug.case
            $flat = static::translationKey($case);
            $translated = $adapter->translate($flat, [], $locale);
            if ($translated !== null) {
                return $translated;
            }
        }

        return null;
    }

    private function resolveTranslatorAdapter(): ?TranslatorAdapter
    {
        // Defensive: when Laravel isn't booted (raw unit tests of traits)
        // there's no container to resolve from. Skip cleanly.
        if (! function_exists('app')) {
            return null;
        }

        try {
            $adapter = app(TranslatorAdapter::class);
        } catch (\Throwable) {
            return null;
        }

        return $adapter instanceof TranslatorAdapter
            ? $adapter
            : null;
    }

    private function caseKey(): string
    {
        /** @var UnitEnum $self */
        $self = $this;
        if ($self instanceof BackedEnum && is_string($self->value)) {
            return $self->value;
        }
        if ($self instanceof BackedEnum && is_int($self->value)) {
            return (string) $self->value;
        }

        return $self->name;
    }

    private function caseName(): string
    {
        /** @var UnitEnum $self */
        $self = $this;

        return $self->name;
    }

    /**
     * Defer to HasAttributes (string field name → resolved value).
     */
    abstract protected function resolvedAttribute(string $field): ?string;
}
