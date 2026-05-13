<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Cast a JSON column to/from a Collection of enum instances.
 *
 * Renamed from "AsEnumCollection" to avoid clashing with Laravel 13's
 * built-in `AsEnumCollection` cast.
 *
 * @implements CastsAttributes<Collection, iterable>
 */
final class AsEnumeratorCollection implements CastsAttributes
{
    /**
     * @param  class-string  $enumClass
     */
    public function __construct(public readonly string $enumClass) {}

    public static function of(string $enumClass): string
    {
        return self::class . ':' . $enumClass;
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): Collection
    {
        if ($value === null || $value === '') {
            return new Collection;
        }

        $decoded = is_string($value) ? json_decode($value, true) : $value;
        if (! is_array($decoded)) {
            return new Collection;
        }

        $cast = new AsEnum($this->enumClass);
        $items = [];
        foreach ($decoded as $raw) {
            $items[] = $cast->get($model, $key, $raw, $attributes);
        }

        return new Collection(array_filter($items));
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if ($value === null) {
            return null;
        }
        if (! is_iterable($value)) {
            return null;
        }

        $cast = new AsEnum($this->enumClass);
        $values = [];
        foreach ($value as $item) {
            $values[] = $cast->set($model, $key, $item, $attributes);
        }

        return json_encode(array_values(array_filter(
            $values,
            static fn (mixed $v): bool => $v !== null,
        )), JSON_THROW_ON_ERROR);
    }
}
