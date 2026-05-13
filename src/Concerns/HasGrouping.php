<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Concerns;

/**
 * Declarative grouping for enums where cases fall into broader buckets
 * (positive/negative/pending, terminal/non-terminal, success/failure, etc.).
 *
 * Implementing enums override `groups()` to return:
 *
 *     return [
 *         'positive' => [self::Published],
 *         'negative' => [self::Rejected, self::Trash],
 *         'pending'  => [self::Draft, self::Pending],
 *     ];
 *
 * Reading: `$case->inGroup('positive')`, `Status::group('positive')` returns
 * the list of cases in that group, `Status::groupedByType()` returns the
 * full bucket map keyed by group name.
 */
trait HasGrouping
{
    /**
     * @return array<string, array<int, static>>
     */
    public static function groups(): array
    {
        return [];
    }

    public function inGroup(string $name): bool
    {
        foreach (static::groups()[$name] ?? [] as $member) {
            if ($member === $this) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<int, static>
     */
    public static function group(string $name): array
    {
        return static::groups()[$name] ?? [];
    }

    /**
     * @return array<string, array<int, static>>
     */
    public static function groupedByType(): array
    {
        return static::groups();
    }

    /**
     * Convenience helpers that match the most common group names.
     */
    public function isPositive(): bool
    {
        return $this->inGroup('positive');
    }

    public function isNegative(): bool
    {
        return $this->inGroup('negative');
    }

    public function isPending(): bool
    {
        return $this->inGroup('pending');
    }
}
