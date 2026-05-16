<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Contracts;

/**
 * Stateful enums describe a finite state machine — a set of allowed
 * cases (`transitions()`), permitted initial states (`initialStates()`),
 * and instance-level operations to navigate them.
 *
 * The six instance-level methods (`transitionTo` etc.) are provided
 * by `Concerns\HasTransitions`. They're declared on this interface
 * as `@method` PHPDoc annotations rather than PHP-level method
 * signatures because the trait uses `self` (narrower) in its
 * parameter types, which is incompatible with a parameter-typed
 * interface signature under PHP's contravariance rule. The PHPDoc
 * declarations give PHPStan + IDEs the type-information they need
 * without forcing every consumer to match an exact signature.
 *
 * Consumers should `use HasTransitions;` to get the six methods —
 * implementing them by hand is unsupported per D34 / D54 (see
 * `.design/_private/NEXT-SESSION.md`).
 *
 * v0.5.0 (PR-β2): added the `@method` PHPDoc declarations so
 * PHPStan stops flagging trait-method calls in trait-consuming code
 * (e.g., `Integrations\Livewire\WithEnumTransitions`). v0.1.0..v0.4.x
 * shipped with the trait providing these methods unannotated. BC-safe.
 *
 * @method array<int, static> allowedTransitions() Cases this case can transition to. Reads `transitions()` for the current case's key.
 * @method bool canTransitionTo(self $target) Predicate: is `$target` in `allowedTransitions()`?
 * @method static transitionTo(self $target, ?TransitionHook $hook = null) Transition to `$target` or throw InvalidTransitionException.
 * @method static|null tryTransitionTo(self $target, ?TransitionHook $hook = null) Non-throwing variant of `transitionTo`.
 * @method bool isTerminal() Predicate: does this case have any outgoing transitions?
 * @method bool isInitialState() Predicate: is this case in `initialStates()`?
 */
interface Stateful extends Enumerator
{
    /**
     * Map of from-value => list of allowed target cases.
     *
     * @return array<int|string, array<int, static>>
     */
    public static function transitions(): array;

    /**
     * Cases allowed as the initial state.
     *
     * @return array<int, static>
     */
    public static function initialStates(): array;
}
