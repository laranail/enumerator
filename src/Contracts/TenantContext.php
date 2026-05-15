<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Contracts;

/**
 * Per-tenant override source for attribute resolution.
 *
 * Consumers implementing this contract bind it via
 * `config('enumerator.tenancy.driver')`. The default
 * `Support\NullTenantContext` preserves single-tenant behaviour.
 *
 * `AttributesOverrideResolver::resolve()` consults the bound
 * `TenantContext` FIRST, then falls back to
 * `config('enumerator.overrides')`, then the compile-time `#[Attribute]`
 * declaration on the case.
 *
 * SECURITY — trusted-input contract for the `icon`, `color`, and
 * `css_class` keys:
 *
 * The Blade base components render `icon` as raw HTML (so consumers
 * can ship inline SVG / `<i>` markup). Implementations of this
 * contract that read `icon` / `color` / `css_class` values from
 * user-editable storage MUST sanitise or token-validate those values
 * before returning them. Returning raw HTML from a request- or
 * admin-editable source is an XSS surface. See
 * `docs/tools/attributes.md` ("Trust contract") for the full
 * discussion and the safe-shape recommendations.
 *
 * `label`, `description`, `help`, and `meta` values pass through
 * Laravel's translator and are HTML-escaped at render time — those
 * keys are safe under runtime override.
 */
interface TenantContext
{
    /**
     * Stable identifier of the currently-active tenant. Null when no
     * tenant context is active (which is the single-tenant default).
     */
    public function currentTenant(): null|string|int;

    /**
     * Per-tenant overrides for the given enum class. Returned shape
     * mirrors `config('enumerator.overrides')`:
     *
     *     [
     *       'CaseName' => [
     *         'color' => 'magenta',
     *         'meta'  => ['paging' => true],
     *       ],
     *     ]
     *
     * Returning an empty array is a valid "no overrides for this
     * tenant + enum combination" signal.
     *
     * @param  class-string  $enumClass
     * @return array<string, array<string, mixed>>
     */
    public function overridesFor(string $enumClass): array;
}
