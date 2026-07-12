<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Eloquent;

use BackedEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Simtabi\Laranail\Enumerator\AbstractEnumeratorClass;
use UnitEnum;

/**
 * Query-scope helpers for enum-cast columns.
 *
 * @mixin Model
 */
trait HasEnumeratorScopes
{
    public function scopeWhereEnum(Builder $query, string $column, UnitEnum|AbstractEnumeratorClass $case): Builder
    {
        return $query->where($column, self::caseToScalar($case));
    }

    public function scopeWhereEnumNot(Builder $query, string $column, UnitEnum|AbstractEnumeratorClass $case): Builder
    {
        return $query->where($column, '!=', self::caseToScalar($case));
    }

    /**
     * @param  array<int, UnitEnum|AbstractEnumeratorClass>  $cases
     */
    public function scopeWhereEnumIn(Builder $query, string $column, array $cases): Builder
    {
        return $query->whereIn($column, array_map(self::caseToScalar(...), $cases));
    }

    /**
     * @param  array<int, UnitEnum|AbstractEnumeratorClass>  $cases
     */
    public function scopeWhereEnumNotIn(Builder $query, string $column, array $cases): Builder
    {
        return $query->whereNotIn($column, array_map(self::caseToScalar(...), $cases));
    }

    public function scopeWhereEnumMeta(Builder $query, string $column, string $key, mixed $value = true): Builder
    {
        // Use Model::getCasts() so Laravel 13 method-style casts() are
        // honoured alongside the legacy $casts property.
        $casts = $this->getCasts();
        /** @var class-string $enumClass */
        $enumClass = $casts[$column] ?? '';
        if ($enumClass === '' || ! enum_exists($enumClass)) {
            return $query->whereRaw('1 = 0');
        }
        $values = [];
        foreach ($enumClass::cases() as $case) {
            if (! method_exists($case, 'meta')) {
                continue;
            }
            if ($case->meta($key) === $value) {
                $values[] = self::caseToScalar($case);
            }
        }
        if ($values === []) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereIn($column, $values);
    }

    private static function caseToScalar(UnitEnum|AbstractEnumeratorClass $case): string|int
    {
        if ($case instanceof BackedEnum) {
            return $case->value;
        }
        if ($case instanceof UnitEnum) {
            return $case->name;
        }

        /** @var string|int */
        return $case->getValue();
    }
}
