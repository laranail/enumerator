<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Concerns;

use BackedEnum;
use BadMethodCallException;

/**
 * Opt-in trait that lets consumers call cases as if they were static methods:
 *
 *     Status::Active()       // 'active'
 *     Status::Active('label') // 'Active'
 *
 * Inspired by archtechx/enums' InvokableCases. Configurable via
 * `config('enumerator.magic.allow_invokable_cases')`. Disabled by default;
 * traits add this only when explicitly used.
 *
 * Implements `__callStatic` — do not combine with another trait that does so
 * unless they cooperate.
 */
trait HasInvokableCases
{
    /**
     * @param  array<int, mixed>  $arguments
     */
    public static function __callStatic(string $name, array $arguments): mixed
    {
        foreach (static::cases() as $case) {
            if ($case->name !== $name) {
                continue;
            }

            return $case->whenInvoked(...$arguments);
        }

        throw new BadMethodCallException(sprintf(
            'No case "%s" on %s.',
            $name,
            static::class,
        ));
    }

    /**
     * Invocation hook. Default behaviour returns the backed value for
     * backed enums or the case name for pure enums. Override per-enum for
     * custom invocation semantics.
     */
    public function whenInvoked(mixed ...$arguments): mixed
    {
        if ($arguments === []) {
            return $this instanceof BackedEnum ? $this->value : $this->name;
        }

        if ($arguments[0] === 'label' && method_exists($this, 'label')) {
            return $this->label();
        }

        return $this instanceof BackedEnum ? $this->value : $this->name;
    }
}
