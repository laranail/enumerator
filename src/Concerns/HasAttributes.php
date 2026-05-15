<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Concerns;

use Simtabi\Laranail\Enumerator\Support\AttributeBag;
use Simtabi\Laranail\Enumerator\Support\AttributesCache;
use Simtabi\Laranail\Enumerator\Support\EnumeratorRegistry;

/**
 * Reads `#[Label]` / `#[Description]` / `#[Color]` / `#[Icon]` / `#[Help]` /
 * `#[Order]` / `#[Bit]` / `#[Meta]` / `#[CssClass]` attributes from the case
 * declaration, with config-file override support.
 *
 * Override priority (per attribute key, e.g. "color"):
 *   1. config('enumerator.overrides.{FQCN}.{CaseName}.color')
 *   2. The compile-time #[Color] attribute on the case
 *   3. null
 *
 * `meta` is merged shallowly with the config override (override wins on key
 * conflict) rather than fully replacing the attribute declaration.
 */
trait HasAttributes
{
    public function color(): ?string
    {
        return $this->resolvedAttribute('color');
    }

    public function icon(): ?string
    {
        return $this->resolvedAttribute('icon');
    }

    public function order(): ?int
    {
        $override = $this->overrideResolve('order');
        if (is_int($override)) {
            return $override;
        }
        if (is_string($override) && is_numeric($override)) {
            return (int) $override;
        }
        if (is_float($override)) {
            return (int) $override;
        }

        return $this->attributeBag()->order;
    }

    public function cssClass(?string $framework = null): ?string
    {
        if ($framework === null) {
            $framework = 'plain';
            if (function_exists('config')) {
                $configured = config('enumerator.css_framework');
                if (is_string($configured) && $configured !== '') {
                    $framework = $configured;
                }
            }
        }
        $override = $this->overrideResolve('css_class.' . $framework);
        if (is_string($override)) {
            return $override;
        }

        return $this->attributeBag()->cssClassFor($framework);
    }

    /**
     * Whole meta array merged with config overrides.
     *
     * @return array<string, mixed>
     */
    public function metaAll(): array
    {
        $bag = $this->attributeBag();
        $declared = $bag->meta;
        $resolver = EnumeratorRegistry::instance()?->overrides;
        $merged = $resolver?->mergeMeta($this, $declared);

        return $merged ?? $declared ?? [];
    }

    /**
     * Read a single meta key. Returns null when neither the case nor the
     * config override declares the key.
     */
    public function meta(string $key): mixed
    {
        return $this->metaAll()[$key] ?? null;
    }

    /**
     * @return array<string, mixed>
     */
    public function attributes(): array
    {
        $bag = $this->attributeBag();

        return array_filter([
            'label' => $bag->label,
            'description' => $bag->description,
            'color' => $bag->color,
            'icon' => $bag->icon,
            'help' => $bag->help,
            'order' => $bag->order,
            'bit' => $bag->bit,
            'meta' => $bag->meta,
            'css_classes' => $bag->cssClasses,
        ], static fn (mixed $v): bool => $v !== null && $v !== []);
    }

    public static function classDescription(): ?string
    {
        return AttributesCache::forClass(static::class)->description;
    }

    /**
     * Internal: pull the resolved string attribute (config-override first,
     * then declared attribute, then null).
     */
    protected function resolvedAttribute(string $field): ?string
    {
        $override = $this->overrideResolve($field);
        if (is_string($override) && $override !== '') {
            return $override;
        }

        $bag = $this->attributeBag();

        return match ($field) {
            'label' => $bag->label,
            'description' => $bag->description,
            'color' => $bag->color,
            'icon' => $bag->icon,
            'help' => $bag->help,
            default => null,
        };
    }

    /**
     * Config override lookup; returns null when no registry is bound (e.g.
     * in non-Laravel unit tests).
     */
    private function overrideResolve(string $key): mixed
    {
        $registry = EnumeratorRegistry::instance();
        if ($registry === null) {
            return null;
        }

        return $registry->overrides->resolve($this, $key);
    }

    private function attributeBag(): AttributeBag
    {
        return AttributesCache::for($this);
    }
}
