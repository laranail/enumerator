<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Blade\Components\Concerns;

/**
 * Resolves which framework view to render for a given component. Class strings
 * themselves live inside the framework view files at
 * `resources/views/components/{framework}/{component}.blade.php`.
 *
 * Resolution order for the framework:
 *   1. `$this->framework` (per-call prop on the component tag)
 *   2. `config('enumerator.css_framework')`
 *   3. 'plain'
 *
 * Consumers customize by either:
 *   - editing the per-call props (`framework=`, `class=`, `:icon-classes=`),
 *   - publishing the framework's view bundle and editing it,
 *   - setting long-lived overrides under `config('enumerator.element_overrides')`.
 */
trait RoutesToFrameworkView
{
    /**
     * Resolve the framework view path for the given component (e.g. 'badge').
     */
    protected function frameworkView(string $component): string
    {
        $ns = (string) (config('enumerator.view_namespace') ?? 'laranail-enumerator');
        $framework = $this->framework
            ?? (string) (config('enumerator.css_framework') ?? 'plain');

        // Fall back to the plain bundle when an unknown framework is requested.
        $allowed = ['plain', 'tailwind', 'daisyui', 'bootstrap', 'bulma'];
        if (! in_array($framework, $allowed, true)) {
            $framework = 'plain';
        }

        return $ns . '::components.' . $framework . '.' . $component;
    }

    /**
     * Extract the consumer's `class="..."` HTML attribute as a plain string.
     * Framework views append this after their default class set, so the
     * caller's classes win the cascade.
     *
     * Trait consumers are required to be `Illuminate\View\Component`
     * subclasses (which always have `$attributes`). The null-guard for
     * `$this->attributes` is preserved because Laravel sets the property
     * after component construction.
     */
    protected function consumerClasses(): string
    {
        if ($this->attributes === null) {
            return '';
        }

        return trim((string) ($this->attributes->get('class') ?? ''));
    }

    /**
     * Extract the consumer's `id="..."` HTML attribute (used when no rootId
     * override prop is set).
     */
    protected function consumerId(): ?string
    {
        if ($this->attributes === null) {
            return null;
        }
        $id = $this->attributes->get('id');

        return is_string($id) && $id !== '' ? $id : null;
    }
}
