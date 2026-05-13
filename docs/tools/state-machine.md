# State machine

Implement `Contracts\Stateful` and `use HasTransitions`:

```php
enum OrderStatusEnum: string implements Enumerator, Stateful {
    use HasEnumeratorBehavior, HasTransitions;
    case Pending = 'pending'; case Paid = 'paid'; /* ... */

    public static function initialStates(): array { return [self::Pending]; }
    public static function transitions(): array {
        return [
            self::Pending->value => [self::Paid, self::Cancelled],
            self::Paid->value    => [self::Shipped],
            /* ... */
        ];
    }
}
```

Enforce on the model via `use HasEnumeratorStateMachine;` and `protected array $stateMachines = ['status'];`.

History records in `enumerator_state_history` (publish migration first).
