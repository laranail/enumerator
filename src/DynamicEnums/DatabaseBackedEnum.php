<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\DynamicEnums;

use Illuminate\Support\Facades\DB;
use Simtabi\Laranail\Enumerator\AbstractEnumeratorClass;
use Simtabi\Laranail\Enumerator\Support\AttributesCache;
use Simtabi\Laranail\Enumerator\Support\CasesCache;

/**
 * ⚠ DESIGN CAVEAT (): instances of subclasses of
 * `DatabaseBackedEnum` are NOT native PHP enums.
 *
 * - `match($x) { Foo::Bar => … }` will NOT exhaust-check
 * - `function f(BackedEnum $b)` will NOT accept these
 * - Refactoring tools that understand native enums (Rector etc.) will not
 *   understand these
 *
 * **Use the native enum path** (`enum X: string implements Enumerator { use
 * HasEnumerator; … }`) unless your case set genuinely needs to be defined
 * at runtime — CMS-driven taxonomies, tenant-customisable lifecycle
 * states, plugin-contributed status sets.
 *
 * ---
 *
 * Implementation: subclass declares `table()` and (optionally)
 * `nameColumn()` / `valueColumn()`. Call `static::loadCases()` once per
 * process — typically in a service provider boot or in the Octane
 * warmup listener (`Modules\Octane\WarmCachesListener`). Cases are
 * frozen for the lifetime of the process; rebuild with `reloadCases()`.
 *
 * The loaded cases flow through `CasesCache::setConstants()`, which
 * means all standard `AbstractEnumeratorClass` machinery (cases(),
 * fromValue(), labels(), invokable cases) works without further glue.
 *
 *     class TenantStatus extends DatabaseBackedEnum
 *     {
 *         protected static function table(): string
 *         {
 *             return 'tenant_statuses';
 *         }
 *     }
 *
 *     // In your boot:
 *     TenantStatus::loadCases();
 *
 *     // Anywhere:
 *     TenantStatus::ACTIVE();   // works
 *     TenantStatus::cases();    // hydrated from the table
 */
abstract class DatabaseBackedEnum extends AbstractEnumeratorClass
{
    /**
     * Table holding the case definitions. Required override.
     */
    abstract protected static function table(): string;

    /**
     * Column storing the case name (constant-like identifier).
     */
    protected static function nameColumn(): string
    {
        return 'name';
    }

    /**
     * Column storing the case value.
     */
    protected static function valueColumn(): string
    {
        return 'value';
    }

    /**
     * Load cases from the configured table and register them with
     * `CasesCache` so the rest of the enumerator API works seamlessly.
     *
     * Idempotent: subsequent calls re-read the table. Use `reloadCases()`
     * to force a refresh after seeding new rows mid-process.
     *
     * @throws \Throwable when the DB connection / table is unavailable
     */
    public static function loadCases(): void
    {
        $rows = DB::table(static::table())
            ->select([static::nameColumn(), static::valueColumn()])
            ->get();

        $map = [];
        foreach ($rows as $row) {
            $name = (string) $row->{static::nameColumn()};
            /** @var string|int $value */
            $value = $row->{static::valueColumn()};
            $map[$name] = $value;
        }

        CasesCache::setConstants(static::class, $map);
    }

    /**
     * Force a refresh of the loaded cases. Useful in dev/test or after
     * the consumer seeds new rows mid-process.
     */
    public static function reloadCases(): void
    {
        // Flush both reflection caches so any AttributeBag we'd built
        // for now-stale cases doesn't linger.
        CasesCache::flush();
        AttributesCache::flush();
        static::loadCases();
    }
}
