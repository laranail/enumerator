# Livewire integration

Backed-enum properties work out of the box in Livewire 3.5+ via Laravel's native enum casting.

For pure enums or `AbstractEnumeratorClass` instances, use `EnumeratorCasts::hydrateProperty()` in your component's `hydrate{Prop}` hook.

## Per-input `wire:model` on form components (v0.3.0)

`<x-laranail-enumerator::radio>` and `<x-laranail-enumerator::checkboxes>` forward `wire:model` (and an optional modifier) onto each rendered `<input>`, so a single component picks up Livewire two-way binding without manual `<input>` plumbing.

```blade
{{-- radio with .live so changes round-trip immediately --}}
<x-laranail-enumerator::radio
    :enum="\App\Enums\OrderStatus::class"
    name="status"
    wire-model="status"
    wire-model-modifier="live"
/>

{{-- checkboxes for a multi-value array property --}}
<x-laranail-enumerator::checkboxes
    :enum="\App\Enums\Permission::class"
    name="permissions[]"
    wire-model="permissions"
/>
```

Each rendered `<input>` carries `wire:model.live="status"` / `wire:model="permissions"`. The dropdown component (which renders an Alpine listbox, not a native `<select>`) uses its own seeded-state + `:selected` prop path — see [Dropdown component docs](../tools/blade-components.md) for the multi-select shape.

## State-machine transitions: `WithEnumTransitions` trait (v0.3.0)

`Integrations\Livewire\WithEnumTransitions` promotes a `Stateful`-implementing enum property into a one-liner transition surface:

```php
use Livewire\Component;
use Simtabi\Laranail\Enumerator\Integrations\Livewire\WithEnumTransitions;

class OrderShow extends Component
{
    use WithEnumTransitions;

    public Order $order;

    public function markPaid(): void
    {
        // transitions $this->order->status to OrderStatus::Paid if allowed
        $this->transitionEnum('order.status', OrderStatus::Paid);
    }

    public function canMarkPaid(): bool
    {
        return $this->canTransitionEnum('order.status', OrderStatus::Paid);
    }
}
```

The trait uses `data_get` / `data_set` so a dotted property path (`order.status`) walks into a nested model or array. Failed transitions add the error to the component's error bag and dispatch a Livewire event for UI feedback. See [WithEnumTransitions reference](../tools/livewire-transitions.md) for the full signature, event names, and error-bag keys.

## Cache-key enums inside Livewire components

Enums using `Concerns\IsCacheKey` (v0.3.0) work the same inside a Livewire component as anywhere else — see [Cache keys](../tools/cache-keys.md). The instance presence probe is `cached()` (not `has()`), which keeps the trait composable with `HasEnumeratorBehavior`'s static `has(target)` membership check on the same enum.
