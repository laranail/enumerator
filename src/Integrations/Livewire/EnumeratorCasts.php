<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Integrations\Livewire;

use Livewire\Component;

if (! class_exists(Component::class)) {
    return;
}

/**
 * Generic helper for casting enum-typed Livewire properties via component
 * hooks. Livewire 3.5+ natively supports BackedEnum property hydration; this
 * helper is for pure enums and AbstractEnumeratorClass instances.
 */
final class EnumeratorCasts
{
    /**
     * @param  class-string  $enumClass
     */
    public static function hydrateProperty(string $enumClass, mixed $value): mixed
    {
        if ($value === null) {
            return null;
        }
        if (enum_exists($enumClass) && method_exists($enumClass, 'tryFromName') && is_string($value)) {
            return $enumClass::tryFromName($value) ?? $value;
        }

        return $value;
    }
}
