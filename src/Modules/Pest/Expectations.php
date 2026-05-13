<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Modules\Pest;

use PHPUnit\Framework\Assert;
use Simtabi\Laranail\Enumerator\Helpers\Bitmask;
use UnitEnum;

/**
 * Custom Pest expectations for enumerator cases.
 *
 * Registered by `PestServiceProvider::boot()` when the module is enabled
 * and Pest is available. Consumers write:
 *
 *     expect($order->status)->toBeCase(OrderStatusEnum::Pending);
 *     expect($user->role)->toBeIn([RoleEnum::Admin, RoleEnum::Editor]);
 *     expect($user->flags)->toHaveBit(FeatureFlagEnum::DarkMode);
 *     expect($order->status)->toCanTransitionTo(OrderStatusEnum::Paid);
 *     expect($a)->toEqualEnum($b);
 *
 * The callbacks below MUST NOT be `static`: Pest binds `$this` to the
 * `Pest\Expectation` instance for `$this->value` access.
 */
final class Expectations
{
    /**
     * Idempotent. Safe to call from boot().
     */
    public static function register(): void
    {
        if (! function_exists('expect')) {
            return;
        }

        expect()->extend('toBeCase', function (UnitEnum $expected): mixed {
            Assert::assertSame(
                $expected,
                $this->value,
                sprintf(
                    'Expected case %s, got %s.',
                    $expected::class . '::' . $expected->name,
                    $this->value instanceof UnitEnum ? $this->value::class . '::' . $this->value->name : var_export($this->value, true),
                ),
            );

            return $this;
        });

        expect()->extend('toBeIn', function (array $cases): mixed {
            Assert::assertTrue(
                $this->value instanceof UnitEnum && in_array($this->value, $cases, true),
                sprintf(
                    'Expected one of [%s], got %s.',
                    implode(', ', array_map(static fn (UnitEnum $c): string => $c::class . '::' . $c->name, $cases)),
                    $this->value instanceof UnitEnum ? $this->value::class . '::' . $this->value->name : var_export($this->value, true),
                ),
            );

            return $this;
        });

        expect()->extend('toEqualEnum', function (mixed $other): mixed {
            $value = $this->value;
            Assert::assertTrue(
                $value instanceof UnitEnum
                    && method_exists($value, 'is')
                    && $value->is($other),
                sprintf('Expected enum to equal %s.', var_export($other, true)),
            );

            return $this;
        });

        expect()->extend('toHaveBit', function (UnitEnum $bit): mixed {
            $mask = $this->value;
            Assert::assertTrue(
                $mask instanceof Bitmask && $mask->has($bit),
                sprintf('Expected mask to contain %s.', $bit::class . '::' . $bit->name),
            );

            return $this;
        });

        expect()->extend('toCanTransitionTo', function (UnitEnum $target): mixed {
            $value = $this->value;
            Assert::assertTrue(
                $value instanceof UnitEnum
                    && method_exists($value, 'canTransitionTo')
                    && $value->canTransitionTo($target),
                sprintf(
                    'Expected transition to %s to be allowed from %s.',
                    $target::class . '::' . $target->name,
                    $value instanceof UnitEnum ? $value::class . '::' . $value->name : var_export($value, true),
                ),
            );

            return $this;
        });
    }
}
