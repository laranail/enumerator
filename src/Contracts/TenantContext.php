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
