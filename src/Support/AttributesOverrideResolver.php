<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Support;

use Illuminate\Contracts\Config\Repository as Config;
use Simtabi\Laranail\Enumerator\Contracts\Enumerator;
use Simtabi\Laranail\Enumerator\Contracts\TenantContext;
use UnitEnum;

/**
 * Reads attribute overrides from `TenantContext` then
 * `config('enumerator.overrides')`.
 *
 * Lookup priority for any attribute key (color, icon, etc.):
 *   1. TenantContext::overridesFor($class)[$caseName][$key]  (when bound)
 *   2. config('enumerator.overrides.{FQCN}.{CaseName}.{key}')
 *   3. The compile-time #[Attribute] declaration (caller's fallback).
 *
 * Overrides for the `meta` key are merged shallowly with the attribute
 * declarations rather than fully replacing them.
 */
final class AttributesOverrideResolver
{
    public function __construct(
        private readonly Config $config,
        private readonly ?TenantContext $tenantContext = null,
    ) {}

    /**
     * Resolve the active TenantContext. Constructor-injected one wins
     * (kept for unit-test isolation); otherwise resolves from the
     * container at call time so tests can rebind via
     * `app()->instance()` between assertions.
     */
    private function tenant(): ?TenantContext
    {
        if ($this->tenantContext !== null) {
            return $this->tenantContext;
        }
        if (! function_exists('app')) {
            return null;
        }
        try {
            $bound = app(TenantContext::class);
        } catch (\Throwable) {
            return null;
        }

        return $bound instanceof TenantContext ? $bound : null;
    }

    /**
     * Return the override value for a single attribute key, or null if not
     * configured.
     *
     * @param  UnitEnum|object  $caseOrInstance  enum case or AbstractEnumeratorClass instance
     */
    public function resolve(object $caseOrInstance, string $key): mixed
    {
        $entry = $this->entryFor($caseOrInstance);
        if ($entry === null) {
            return null;
        }

        return $entry[$key] ?? null;
    }

    /**
     * Merge an override 'meta' array into the case's declared meta.
     *
     * @param  UnitEnum|object  $caseOrInstance  enum case or AbstractEnumeratorClass instance
     * @param  array<string, mixed>|null  $declared
     * @return array<string, mixed>|null
     */
    public function mergeMeta(object $caseOrInstance, ?array $declared): ?array
    {
        $entry = $this->entryFor($caseOrInstance);
        $override = is_array($entry) && isset($entry['meta']) && is_array($entry['meta'])
            ? $entry['meta']
            : null;

        if ($declared === null && $override === null) {
            return null;
        }

        return array_merge($declared ?? [], $override ?? []);
    }

    /**
     * @return array<string, mixed>|null
     */
    private function entryFor(object $caseOrInstance): ?array
    {
        $class = $caseOrInstance::class;
        $name = $caseOrInstance instanceof UnitEnum
            ? $caseOrInstance->name
            : (method_exists($caseOrInstance, 'getKey') ? (string) $caseOrInstance->getKey() : null);
        if ($name === null) {
            return null;
        }

        // TenantContext-scoped overrides take precedence.
        $tenant = $this->tenant();
        if ($tenant !== null) {
            $tenantOverrides = $tenant->overridesFor($class);
            $tenantEntry = $tenantOverrides[$name] ?? null;
            if (is_array($tenantEntry) && $tenantEntry !== []) {
                return $tenantEntry;
            }
        }

        /** @var array<class-string, array<string, array<string, mixed>>> $all */
        $all = (array) $this->config->get('enumerator.overrides', []);
        $entry = $all[$class][$name] ?? null;

        return is_array($entry) ? $entry : null;
    }
}
