<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Contracts;

/**
 * Marker contract for enums whose cases serve as cache-key namespaces.
 *
 * The `Concerns\IsCacheKey` trait provides a default implementation
 * (`{value}` for backed enums, `{name}` for pure enums). Consumers can
 * also implement `Cacheable` directly without the trait — useful when
 * the key has a non-trivial prefix / shape:
 *
 *     enum SettingsKey: string implements Cacheable
 *     {
 *         case ThemeColor = 'theme.color';
 *
 *         public function key(): string
 *         {
 *             return 'settings:' . $this->value;
 *         }
 *     }
 *
 *     SettingsKey::ThemeColor->key();   // "settings:theme.color"
 */
interface Cacheable
{
    /**
     * The cache-key string for this case. Used by Laravel's Cache
     * facade when the case is invoked via the IsCacheKey trait's
     * put / get / forget / has / remember methods.
     */
    public function key(): string;
}
