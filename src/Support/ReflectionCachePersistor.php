<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Support;

/**
 * Bridges the per-process reflection caches (`AttributesCache` +
 * `CasesCache`) to the disk-backed `LayeredCache`.
 *
 * Uses the snapshot-persistor pattern (NOT write-through):
 *
 *   - `dump()` warms the in-memory memos for a list of classes, then
 *     persists a single payload via `LayeredCache::persist()`.
 *   - `restore()` reads the payload back at boot and rehydrates the
 *     in-memory memos.
 *
 * Why snapshot, not write-through? Write-through would couple every
 * `AttributesCache::for($case)` call to a key-value store. Per-call
 * latency on the hot path isn't worth it. Snapshot matches Laravel's
 * `config:cache` / `route:cache` idiom: occasionally dump the whole
 * thing, restore once at boot.
 */
final class ReflectionCachePersistor
{
    public function __construct(
        private readonly LayeredCache $store,
    ) {}

    /**
     * Warm the in-memory memos for the given classes, then persist a
     * snapshot via `LayeredCache::persist()`. Idempotent.
     *
     * @param  array<int, class-string>  $classes  enumerator classes to warm
     */
    public function dump(array $classes): void
    {
        foreach ($classes as $class) {
            if (! is_string($class) || ! IsEnumeratorClass::check($class)) {
                continue;
            }
            if (enum_exists($class)) {
                CasesCache::nativeCases($class);
            }
            foreach (IsEnumeratorClass::casesOf($class) as $case) {
                AttributesCache::for($case);
            }
        }

        $this->store->put('reflection.attributes', AttributesCache::snapshot());
        $this->store->put('reflection.cases', CasesCache::snapshot());
        $this->store->persist();
    }

    /**
     * Read the persisted snapshot back into the in-memory memos.
     * Returns true on a hit, false when no snapshot is present.
     */
    public function restore(): bool
    {
        // `remember()` with a null resolver returns null on miss. We
        // store null sentinels by design — see the assertions below.
        $attrs = $this->store->remember('reflection.attributes', static fn (): ?array => null);
        $cases = $this->store->remember('reflection.cases', static fn (): ?array => null);

        if (! is_array($attrs) || ! is_array($cases)) {
            return false;
        }

        AttributesCache::restore($attrs);
        CasesCache::restore($cases);

        return true;
    }
}
