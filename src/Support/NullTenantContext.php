<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Support;

use Simtabi\Laranail\Enumerator\Contracts\TenantContext;

/**
 * Default `TenantContext` implementation.
 *
 * Returns null / empty for every call — preserving single-tenant
 * behaviour. The service provider binds this as the default when
 * `config('enumerator.tenancy.driver')` is unset.
 */
final class NullTenantContext implements TenantContext
{
    public function currentTenant(): null|string|int
    {
        return null;
    }

    public function overridesFor(string $enumClass): array
    {
        unset($enumClass);

        return [];
    }
}
