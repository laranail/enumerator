# Livewire state-transition trait

A Livewire 3 trait that wraps `Stateful::transitionTo()` into a
reactive action with error-bag integration. Introduced in v0.3.0.

Promotes the prior `docs/recipes/livewire.md` state-transition
example to a first-class trait under
`src/Integrations/Livewire/WithEnumTransitions`.

## Setup

The trait is ungated by config — it's available the moment
`livewire/livewire` is installed. Drop into any Livewire component
that holds a `Contracts\Stateful` enum property:

```php
namespace App\Livewire;

use App\Enums\OrderStatus;
use App\Models\Order;
use Livewire\Component;
use Simtabi\Laranail\Enumerator\Integrations\Livewire\WithEnumTransitions;

class OrderShow extends Component
{
    use WithEnumTransitions;

    public Order $order;

    public function pay(): void
    {
        $this->transitionEnum('order.status', OrderStatus::Paid);
    }

    public function ship(): void
    {
        $this->transitionEnum('order.status', OrderStatus::Shipped);
    }

    public function cancel(): void
    {
        $this->transitionEnum('order.status', OrderStatus::Cancelled);
    }

    public function render()
    {
        return view('livewire.order-show');
    }
}
```

The Blade view picks up the error bag via `@error()`:

```blade
<button wire:click="pay"
        wire:loading.attr="disabled"
        @disabled(! $this->canTransitionEnum('order.status', \App\Enums\OrderStatus::Paid))
>
    Pay
</button>

@error('order.status')
    <span class="text-red-500">{{ $message }}</span>
@enderror
```

`wire:loading.attr="disabled"` provides the loading state. The
`@disabled(! $this->canTransitionEnum(...))` pre-flights whether the
transition would succeed and disables the button when it wouldn't.

## Methods

### `transitionEnum(string $propertyPath, UnitEnum|BackedEnum $target): bool`

Advances the enum at `$propertyPath` to the `$target` case.

- Resolves the current value via `data_get($this, $propertyPath)`.
  Dot notation works (`'order.status'` traverses through
  `$this->order->status`).
- Calls `Stateful::transitionTo($target)`.
- On success:
  - Sets the result back to the property via `data_set()`.
  - Dispatches `enumerator.transitioned` Livewire event with
    `{from, to, property}` payload.
  - Returns `true`.
- On `InvalidTransitionException`:
  - Pushes the exception message to the Livewire error bag at
    `$propertyPath`. Does **not** bubble.
  - Returns `false`.
- On non-Stateful property:
  - Pushes an error message to the error bag at `$propertyPath`.
  - Returns `false`.

### `canTransitionEnum(string $propertyPath, UnitEnum|BackedEnum $target): bool`

Pre-flight check. Returns `true` if the transition WOULD succeed,
`false` otherwise. Does **not** mutate state. Useful for button
enable/disable in the view.

## Event dispatch

On success, the trait dispatches:

```js
window.Livewire.on('enumerator.transitioned', (event) => {
    console.log(event.from, event.to, event.property);
});
```

Use this for client-side observability — e.g., toast notifications,
analytics, optimistic UI updates.

## Errors

Failed transitions write to the standard Livewire error bag at the
property path. Blade's `@error('order.status')` picks it up, as does
`$message = $errors->first('order.status')` in vanilla Blade.

The error message comes from `InvalidTransitionException::getMessage()`,
which formats it as:

```
Cannot transition order.status from Pending to Shipped.
```

You can render it directly or wrap with your own message:

```blade
@error('order.status')
    <div class="alert">
        {{ $message }}
        <a href="{{ route('orders.help') }}">Learn why →</a>
    </div>
@enderror
```

## Non-Eloquent / non-property paths

`transitionEnum()` uses `data_get()` / `data_set()` so any Laravel-
supported path resolves: `'user.profile.role'`, `'items.0.status'`,
etc. The current value at the resolved path MUST be a `Stateful` enum
instance — if it's a string, an int, or null, the trait writes an
error to the bag instead of trying to transition.

## Composing with v0.2.x integration

`WithEnumTransitions` composes alongside `EnumeratorCasts`:

```php
class OrderShow extends Component
{
    use WithEnumTransitions;

    public OrderStatus $status;

    // EnumeratorCasts hydration for pure enums:
    public function hydrateStatus(mixed $value): mixed
    {
        return \Simtabi\Laranail\Enumerator\Integrations\Livewire\EnumeratorCasts::hydrateProperty(
            OrderStatus::class,
            $value,
        );
    }
}
```

(Backed enums hydrate natively in Livewire 3.5+; `EnumeratorCasts`
is for pure-enum / `AbstractEnumeratorClass` paths.)

## Bulk transitions (v0.4.0)

`bulkTransitionEnum($paths, $target)` advances multiple Stateful
properties to the same target case in one call. Use it for actions
that fan out to N records — "approve all pending", "ship all paid",
"cancel all queued".

```php
public function shipAll(): void
{
    $this->bulkTransitionEnum(
        ['primaryOrder.status', 'secondaryOrder.status', 'shipment.status'],
        OrderStatus::Shipped,
    );
}
```

Per-path failures are independent. A path whose current value can't
reach the target adds an error to the bag at THAT path; the other
paths still advance. The method returns `true` only when every
path transitioned successfully; otherwise `false`. An empty path
list is a no-op that returns `true`.

## Custom error messages (v0.4.0)

`transitionEnumOrValidate($path, $target, $messages)` matches
`transitionEnum()`'s semantics but lets the caller override the
default error text on failure — useful when the framework-default
message ("Cannot transition Pending → Shipped") doesn't fit the
UX copy:

```php
public function pay(): void
{
    $this->transitionEnumOrValidate(
        'order.status',
        OrderStatus::Paid,
        messages: [
            'invalid' => 'Please complete the previous step first.',
            'notStateful' => 'Order is not in a transition-aware state.',
        ],
    );
}
```

`messages['invalid']` replaces the `InvalidTransitionException`
message; `messages['notStateful']` replaces the "not a Stateful
enum case" message. Either key is optional; omitted keys fall
through to the default text. Behaviour on success is identical
to `transitionEnum()` — property advances, `enumerator.transitioned`
event dispatches.

## See also

- [State machine](state-machine.md) — `Stateful` contract + `HasTransitions` trait
- [Contracts](contracts.md) — `Stateful` reference
- [Blade components](blade-components.md) — Livewire-aware select / radio / checkboxes
- [`docs/recipes/livewire.md`](../recipes/livewire.md) — full Livewire form recipe

---

[← Docs index](../../README.md#documentation)
