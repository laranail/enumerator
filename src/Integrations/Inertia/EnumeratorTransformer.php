<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Integrations\Inertia;

use BackedEnum;
use Simtabi\Laranail\Enumerator\AbstractEnumeratorClass;
use UnitEnum;

/**
 * Helper for shaping enumerator cases for SPA consumption. Pair with
 * Inertia's `shareProps()` or in a controller response:
 *
 *     return inertia('User', [
 *         'user' => $user,
 *         'status' => EnumeratorTransformer::case($user->status),
 *         'statusOptions' => EnumeratorTransformer::options(UserStatusEnum::class),
 *     ]);
 */
final class EnumeratorTransformer
{
    /**
     * @return array{value: string|int|null, name: string|null, label: string}|null
     */
    public static function case(UnitEnum|AbstractEnumeratorClass|null $case): ?array
    {
        if ($case === null) {
            return null;
        }

        $value = match (true) {
            $case instanceof BackedEnum => $case->value,
            $case instanceof UnitEnum => $case->name,
            default => $case->getValue(),
        };

        $name = $case instanceof UnitEnum
            ? $case->name
            : ($case instanceof AbstractEnumeratorClass ? $case->getKey() : null);

        return [
            'value' => $value,
            'name' => $name,
            'label' => method_exists($case, 'label') ? (string) $case->label() : (string) ($name ?? ''),
        ];
    }

    /**
     * @param  class-string  $enumClass
     * @return array<int, array{value: string|int|null, name: string|null, label: string}>
     */
    public static function options(string $enumClass): array
    {
        if (! enum_exists($enumClass)) {
            return [];
        }
        $out = [];
        foreach ($enumClass::cases() as $case) {
            $shape = self::case($case);
            if ($shape !== null) {
                $out[] = $shape;
            }
        }

        return $out;
    }
}
