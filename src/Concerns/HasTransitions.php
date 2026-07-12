<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Concerns;

use BackedEnum;
use Simtabi\Laranail\Enumerator\Contracts\Stateful;
use Simtabi\Laranail\Enumerator\Contracts\TransitionHook;
use Simtabi\Laranail\Enumerator\Exceptions\InvalidTransitionException;
use UnitEnum;

/**
 * State machine helpers for native enums implementing
 * Contracts\Stateful.
 *
 * Implementers declare:
 *   - public static function transitions(): array  — map of from-value => target cases
 *   - public static function initialStates(): array — cases allowed as the initial state
 *
 * This trait provides the runtime: canTransitionTo, transitionTo (throws on
 * disallowed), tryTransitionTo (null on disallowed), allowedTransitions, and
 * isTerminal.
 *
 * @phpstan-require-implements Stateful
 */
trait HasTransitions
{
    /**
     * @return array<int, static>
     */
    public function allowedTransitions(): array
    {
        $key = $this->transitionKey();
        $map = static::transitions();

        /** @var array<int, static> */
        return $map[$key] ?? [];
    }

    public function canTransitionTo(self $target): bool
    {
        foreach ($this->allowedTransitions() as $allowed) {
            if ($allowed === $target) {
                return true;
            }
        }

        return false;
    }

    public function transitionTo(self $target, ?TransitionHook $hook = null): static
    {
        if (! $this->canTransitionTo($target)) {
            throw new InvalidTransitionException(sprintf(
                'Transition from %s::%s to %s::%s is not allowed.',
                static::class,
                $this->name,
                static::class,
                $target->name,
            ));
        }

        if ($hook !== null && ! $hook->before($this, $target)) {
            throw new InvalidTransitionException(sprintf(
                'Transition from %s::%s to %s::%s was blocked by the hook.',
                static::class,
                $this->name,
                static::class,
                $target->name,
            ));
        }

        $hook?->after($this, $target);

        return $target;
    }

    public function tryTransitionTo(self $target, ?TransitionHook $hook = null): ?static
    {
        return $this->canTransitionTo($target) ? $this->transitionTo($target, $hook) : null;
    }

    public function isTerminal(): bool
    {
        return $this->allowedTransitions() === [];
    }

    public function isInitialState(): bool
    {
        return in_array($this, static::initialStates(), true);
    }

    /**
     * Build a from-key from this case so transition map lookups work for
     * backed and pure enums alike.
     */
    private function transitionKey(): string|int
    {
        /** @phpstan-var UnitEnum $self */
        $self = $this;

        return $self instanceof BackedEnum ? $self->value : $self->name;
    }
}
