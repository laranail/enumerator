<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Modules\Saloon;

use BackedEnum;
use Simtabi\Laranail\Enumerator\AbstractEnumeratorClass;
use UnitEnum;

/**
 * Saloon-friendly value caster for enumerator instances.
 *
 * Saloon's connectors / requests accept plain arrays for headers, query
 * params, and JSON bodies — but downstream code may pass enum instances
 * directly. This caster recursively walks arrays and converts every enum
 * instance it finds into the appropriate scalar (backed value, name, or
 * class-const value).
 *
 * Consumers wire it into their Saloon flow however they want. Common
 * patterns:
 *
 *     // In a Connector
 *     protected function defaultHeaders(): array
 *     {
 *         return EnumCaster::cast(['X-Status' => $this->status]);
 *     }
 *
 *     // In a Request
 *     protected function defaultBody(): array
 *     {
 *         return EnumCaster::cast(['role' => $user->role, 'flags' => $user->flags]);
 *     }
 *
 * Plain PHP — no Saloon SDK dependency at the file level. The module's
 * `SaloonServiceProvider` gates registration on config; the caster
 * itself is always available when consumers import it directly.
 */
final class EnumCaster
{
    /**
     * Serialize a single value. Pass-through for non-enum inputs.
     */
    public static function serialize(mixed $value): mixed
    {
        if ($value instanceof BackedEnum) {
            return $value->value;
        }
        if ($value instanceof UnitEnum) {
            return $value->name;
        }
        if ($value instanceof AbstractEnumeratorClass && method_exists($value, 'getValue')) {
            /** @var string|int|null $resolved */
            $resolved = $value->getValue();

            return $resolved;
        }

        return $value;
    }

    /**
     * Recursively cast an array — useful for headers/query/body arrays
     * that contain enum instances nested at arbitrary depth.
     *
     * @param  array<int|string, mixed>  $payload
     * @return array<int|string, mixed>
     */
    public static function cast(array $payload): array
    {
        $out = [];
        foreach ($payload as $key => $value) {
            if (is_array($value)) {
                $out[$key] = self::cast($value);
            } else {
                $out[$key] = self::serialize($value);
            }
        }

        return $out;
    }
}
