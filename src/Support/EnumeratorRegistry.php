<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Support;

/**
 * Singleton registry resolved by the service container. Holds the shared
 * LayeredCache and the AttributesOverrideResolver so concerns can reach them
 * without coupling to the container.
 *
 * The provider populates the singleton at boot; tests can rebind it.
 */
final class EnumeratorRegistry
{
    private static ?self $instance = null;

    public function __construct(
        public readonly LayeredCache $cache,
        public readonly AttributesOverrideResolver $overrides,
    ) {}

    /**
     * Method accessor for the cache singleton. The `Facade\Enumerator::cache()`
     * shim relies on this — facades dispatch to methods, not properties.
     */
    public function cache(): LayeredCache
    {
        return $this->cache;
    }

    /**
     * Method accessor for the override resolver. Same rationale as `cache()`.
     */
    public function overrides(): AttributesOverrideResolver
    {
        return $this->overrides;
    }

    public static function set(self $instance): void
    {
        self::$instance = $instance;
    }

    public static function instance(): ?self
    {
        return self::$instance;
    }

    public static function reset(): void
    {
        self::$instance = null;
    }
}
