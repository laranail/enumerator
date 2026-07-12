<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Support;

/**
 * Shared `[value => label]` options builder used by the framework
 * integrations (Filament filter / radio / select, Nova field) — .
 *
 * Supports both native enums AND `AbstractEnumeratorClass` subclasses
 * via the `IsEnumeratorClass` helper.
 */
final class OptionsArrayBuilder
{
    /**
     * Build a `[value|name => label]` map for the given enum class.
     * Returns an empty array if the class isn't enumerator-shaped.
     *
     * @param  class-string  $enumClass
     * @return array<int|string, string>
     */
    public static function for(string $enumClass): array
    {
        if (! IsEnumeratorClass::check($enumClass)) {
            return [];
        }

        $options = [];
        foreach (IsEnumeratorClass::casesOf($enumClass) as $case) {
            $key = IsEnumeratorClass::valueOf($case);
            $label = method_exists($case, 'label')
                ? (string) $case->label()
                : (string) ($case->name ?? (method_exists($case, 'getKey') ? $case->getKey() : $key));
            $options[$key] = $label;
        }

        return $options;
    }

    /**
     * Flipped variant for hosts (e.g. Nova filters) whose API expects
     * `[label => value]` instead.
     *
     * @param  class-string  $enumClass
     * @return array<string, int|string>
     */
    public static function flipped(string $enumClass): array
    {
        return array_flip(self::for($enumClass));
    }
}
