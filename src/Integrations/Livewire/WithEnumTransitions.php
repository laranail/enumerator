<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Integrations\Livewire;

use BackedEnum;
use Livewire\Component;
use Simtabi\Laranail\Enumerator\Contracts\Stateful;
use Simtabi\Laranail\Enumerator\Exceptions\InvalidTransitionException;
use UnitEnum;

if (! class_exists(Component::class)) {
    return;
}

/**
 * Livewire trait that exposes a reactive `transitionEnum()` action for
 * components holding `Contracts\Stateful` enum properties.
 *
 * Promotes the v0.2.0 `docs/recipes/livewire-state-transitions.md`
 * recipe to a first-class trait. Drop into any Livewire 3 component
 * that exposes a Stateful enum property:
 *
 *     class OrderShow extends \Livewire\Component
 *     {
 *         use \Simtabi\Laranail\Enumerator\Integrations\Livewire\WithEnumTransitions;
 *
 *         public Order $order;
 *
 *         public function pay(): void
 *         {
 *             $this->transitionEnum('order.status', OrderStatusEnum::Paid);
 *         }
 *     }
 *
 * In the Blade:
 *
 *     <button wire:click="pay" wire:loading.attr="disabled">Pay</button>
 *
 *     @error('order.status') <span class="error">{{ $message }}</span> @enderror
 *
 * Behaviour:
 *
 *   - Resolves the property via Livewire's `$this->{property}` access
 *     (uses dot notation: `order.status` resolves through getter chain).
 *   - Calls `Stateful::transitionTo($next)` under the hood.
 *   - On success: the resolved value is set back on the property path,
 *     and a `enumerator.transitioned` Livewire event is dispatched with
 *     `[from, to]` payload.
 *   - On `InvalidTransitionException`: the message is pushed to the
 *     Livewire error bag at the property path so Blade's `@error()`
 *     directive picks it up. No exception bubbles out of the action.
 *
 * Pass `wire:loading.attr="disabled"` (or similar) to the trigger
 * button to get the loading state. The trait doesn't ship UI markup —
 * see the recipe for full examples.
 */
trait WithEnumTransitions
{
    /**
     * Trigger an enum state transition on a property path. Catches
     * InvalidTransitionException and surfaces it via the Livewire error
     * bag instead of bubbling out. Returns true on success, false on
     * failure (so caller actions can short-circuit on failure if they
     * need to).
     *
     * @param  string  $propertyPath  Dot-notation property path on
     *                                this Livewire component, e.g.
     *                                "order.status" or "user.role".
     * @param  UnitEnum|BackedEnum  $target  The enum case to transition to.
     */
    public function transitionEnum(string $propertyPath, UnitEnum|BackedEnum $target): bool
    {
        $current = data_get($this, $propertyPath);

        if (! $current instanceof Stateful) {
            $this->addError($propertyPath, sprintf(
                'Cannot transition: property "%s" is not a Stateful enum case (got %s).',
                $propertyPath,
                $current === null ? 'null' : get_debug_type($current),
            ));

            return false;
        }

        try {
            $next = $current->transitionTo($target);
        } catch (InvalidTransitionException $e) {
            $this->addError($propertyPath, $e->getMessage());

            return false;
        }

        data_set($this, $propertyPath, $next);

        if (method_exists($this, 'dispatch')) {
            $this->dispatch('enumerator.transitioned', [
                'from' => $current,
                'to' => $next,
                'property' => $propertyPath,
            ]);
        }

        return true;
    }

    /**
     * Non-throwing variant — returns the target case if the transition
     * would succeed, or null if not. Doesn't mutate state. Useful for
     * pre-flighting button enable/disable in the view.
     */
    public function canTransitionEnum(string $propertyPath, UnitEnum|BackedEnum $target): bool
    {
        $current = data_get($this, $propertyPath);
        if (! $current instanceof Stateful) {
            return false;
        }

        return $current->canTransitionTo($target);
    }
}
