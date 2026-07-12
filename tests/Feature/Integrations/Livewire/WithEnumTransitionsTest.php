<?php

declare(strict_types=1);

use Illuminate\Support\MessageBag;
use Livewire\Component;
use Simtabi\Laranail\Enumerator\Attributes\Label;
use Simtabi\Laranail\Enumerator\Concerns\HasEnumerator;
use Simtabi\Laranail\Enumerator\Contracts\Enumerator;
use Simtabi\Laranail\Enumerator\Contracts\Stateful;
use Simtabi\Laranail\Enumerator\Integrations\Livewire\WithEnumTransitions;
use Simtabi\Laranail\Enumerator\Tests\Fixtures\Enums\NonStatefulPalette;

// Feature coverage for the v0.3.0 PR-ζ WithEnumTransitions trait.
//
// The trait wraps `Stateful::transitionTo()` for Livewire components
// — on success it mutates the property and dispatches an event; on
// `InvalidTransitionException` it adds the error to the Livewire
// error bag at the property path. The trait itself is the unit under
// test here, so the tests stay focused on the behaviour without
// booting a full Livewire request lifecycle.

enum OrderStatus: string implements Enumerator, Stateful
{
    use HasEnumerator;

    #[Label('Pending')] case Pending = 'pending';
    #[Label('Paid')] case Paid = 'paid';
    #[Label('Shipped')] case Shipped = 'shipped';
    #[Label('Cancelled')] case Cancelled = 'cancelled';

    public static function initialStates(): array
    {
        return [self::Pending];
    }

    public static function transitions(): array
    {
        return [
            self::Pending->value => [self::Paid, self::Cancelled],
            self::Paid->value => [self::Shipped, self::Cancelled],
            self::Shipped->value => [],
            self::Cancelled->value => [],
        ];
    }
}

class OrderShowFixture extends Component
{
    use WithEnumTransitions;

    public OrderStatus $status = OrderStatus::Pending;

    public OrderStatus $secondaryStatus = OrderStatus::Pending;

    public OrderStatus $tertiaryStatus = OrderStatus::Pending;

    /** @var array<string, string> */
    public array $dispatched = [];

    private MessageBag $stubBag;

    public function __construct()
    {
        // parent::__construct() can't be called outside a Livewire
        // lifecycle; skip it for the test fixture and initialize the
        // minimum state the trait needs (error bag + dispatch sink).
        $this->stubBag = new MessageBag;
    }

    public function render(): string
    {
        return '<div></div>';
    }

    // Stub Livewire's getErrorBag()/addError() so the trait's error
    // path doesn't depend on the full Livewire request lifecycle.
    public function getErrorBag(): MessageBag
    {
        return $this->stubBag;
    }

    public function addError($name, $message)
    {
        $this->stubBag->add($name, $message);

        return $this;
    }

    // Stub Livewire's dispatch() so the trait's call doesn't fail.
    public function dispatch($event, ...$params)
    {
        $this->dispatched[(string) $event] = json_encode($params);

        return $this;
    }
}

it('returns true and advances the property on a valid transition', function (): void {
    $component = new OrderShowFixture;
    $component->status = OrderStatus::Pending;

    $ok = $component->transitionEnum('status', OrderStatus::Paid);

    expect($ok)->toBeTrue();
    expect($component->status)->toBe(OrderStatus::Paid);
});

it('returns false and pushes a Livewire error on an invalid transition', function (): void {
    $component = new OrderShowFixture;
    $component->status = OrderStatus::Pending;

    // Pending → Shipped is not allowed (Pending → Paid → Shipped is).
    $ok = $component->transitionEnum('status', OrderStatus::Shipped);

    expect($ok)->toBeFalse();
    expect($component->status)->toBe(OrderStatus::Pending);  // unchanged
    expect($component->getErrorBag()->has('status'))->toBeTrue();
});

it('returns false when the property is not a Stateful instance', function (): void {
    $component = new OrderShowFixture;
    $component->status = OrderStatus::Pending;
    // Replace the property at runtime to simulate a non-Stateful value.
    // PHP enum properties are typed, so we test against the trait's
    // own check by using a property path that doesn't resolve to
    // Stateful: an undefined sub-property.
    $ok = $component->transitionEnum('nonexistentProperty', OrderStatus::Paid);

    expect($ok)->toBeFalse();
    expect($component->getErrorBag()->has('nonexistentProperty'))->toBeTrue();
});

it('canTransitionEnum() pre-flights without mutating state', function (): void {
    $component = new OrderShowFixture;
    $component->status = OrderStatus::Pending;

    expect($component->canTransitionEnum('status', OrderStatus::Paid))->toBeTrue();
    expect($component->canTransitionEnum('status', OrderStatus::Shipped))->toBeFalse();
    expect($component->status)->toBe(OrderStatus::Pending);  // unmutated
});

it('transitionEnum() refuses a non-Stateful target enum (PR-β2 defensive guard)', function (): void {
    // PR-β2 narrows the runtime contract: `$target` must be a
    // Stateful instance. A consumer accidentally passing a non-
    // Stateful UnitEnum (e.g. a pure label enum) now gets an
    // error-bag entry + early return rather than reaching
    // transitionTo() with an incompatible target.
    $component = new OrderShowFixture;
    $component->status = OrderStatus::Pending;

    $ok = $component->transitionEnum('status', NonStatefulPalette::Red);

    expect($ok)->toBeFalse();
    expect($component->status)->toBe(OrderStatus::Pending);  // unchanged
    expect($component->getErrorBag()->has('status'))->toBeTrue();

    $errors = $component->getErrorBag()->get('status');
    expect($errors[0])->toContain('target must be a Stateful enum case');
});

it('canTransitionEnum() returns false for a non-Stateful target', function (): void {
    $component = new OrderShowFixture;
    $component->status = OrderStatus::Pending;

    // Pass a non-Stateful UnitEnum target. PHP's enum union type
    // accepts any UnitEnum here, but canTransitionEnum() must
    // return false rather than reaching $current->canTransitionTo().
    expect($component->canTransitionEnum('status', NonStatefulPalette::Red))->toBeFalse();
});

it('dispatches enumerator.transitioned with from/to payload on success', function (): void {
    $component = new OrderShowFixture;
    $component->status = OrderStatus::Pending;

    $component->transitionEnum('status', OrderStatus::Paid);

    expect($component->dispatched)->toHaveKey('enumerator.transitioned');
    $payload = json_decode($component->dispatched['enumerator.transitioned'], true);
    expect($payload)->toBeArray();
});

it('chained valid transitions advance through the state machine', function (): void {
    $component = new OrderShowFixture;
    $component->status = OrderStatus::Pending;

    expect($component->transitionEnum('status', OrderStatus::Paid))->toBeTrue();
    expect($component->status)->toBe(OrderStatus::Paid);

    expect($component->transitionEnum('status', OrderStatus::Shipped))->toBeTrue();
    expect($component->status)->toBe(OrderStatus::Shipped);

    // Shipped is terminal — no further transitions allowed.
    expect($component->transitionEnum('status', OrderStatus::Paid))->toBeFalse();
    expect($component->status)->toBe(OrderStatus::Shipped);
});

// === PR-ν bulkTransitionEnum + transitionEnumOrValidate (v0.4.0) =========

it('bulkTransitionEnum() advances every path when all transitions are valid', function (): void {
    $component = new OrderShowFixture;

    $ok = $component->bulkTransitionEnum(
        ['status', 'secondaryStatus', 'tertiaryStatus'],
        OrderStatus::Paid,
    );

    expect($ok)->toBeTrue();
    expect($component->status)->toBe(OrderStatus::Paid);
    expect($component->secondaryStatus)->toBe(OrderStatus::Paid);
    expect($component->tertiaryStatus)->toBe(OrderStatus::Paid);
});

it('bulkTransitionEnum() returns false but still advances valid paths when one fails', function (): void {
    $component = new OrderShowFixture;
    $component->status = OrderStatus::Pending;
    // Pre-advance secondaryStatus to Shipped so its transition to Paid
    // becomes invalid (Shipped is terminal). status + tertiaryStatus
    // still transition cleanly.
    $component->secondaryStatus = OrderStatus::Shipped;

    $ok = $component->bulkTransitionEnum(
        ['status', 'secondaryStatus', 'tertiaryStatus'],
        OrderStatus::Paid,
    );

    expect($ok)->toBeFalse();
    expect($component->status)->toBe(OrderStatus::Paid);
    expect($component->secondaryStatus)->toBe(OrderStatus::Shipped);  // unchanged
    expect($component->tertiaryStatus)->toBe(OrderStatus::Paid);
    expect($component->getErrorBag()->has('secondaryStatus'))->toBeTrue();
});

it('bulkTransitionEnum() with empty path list is a no-op true', function (): void {
    $component = new OrderShowFixture;

    expect($component->bulkTransitionEnum([], OrderStatus::Paid))->toBeTrue();
    expect($component->status)->toBe(OrderStatus::Pending);
});

it('transitionEnumOrValidate() uses a custom invalid message when supplied', function (): void {
    $component = new OrderShowFixture;
    $component->status = OrderStatus::Pending;

    $ok = $component->transitionEnumOrValidate(
        'status',
        OrderStatus::Shipped,  // invalid: Pending → Shipped not allowed
        messages: ['invalid' => 'You must pay before shipping.'],
    );

    expect($ok)->toBeFalse();
    expect($component->getErrorBag()->get('status'))->toBe(['You must pay before shipping.']);
});

it('transitionEnumOrValidate() uses a custom notStateful message when supplied', function (): void {
    $component = new OrderShowFixture;

    $ok = $component->transitionEnumOrValidate(
        'nonexistentProperty',
        OrderStatus::Paid,
        messages: ['notStateful' => 'No such order field.'],
    );

    expect($ok)->toBeFalse();
    expect($component->getErrorBag()->get('nonexistentProperty'))->toBe(['No such order field.']);
});

it('transitionEnumOrValidate() falls back to the exception message when no `invalid` key is given', function (): void {
    $component = new OrderShowFixture;
    $component->status = OrderStatus::Pending;

    $ok = $component->transitionEnumOrValidate('status', OrderStatus::Shipped);

    expect($ok)->toBeFalse();
    $errors = $component->getErrorBag()->get('status');
    expect($errors)->not->toBeEmpty();
    // The InvalidTransitionException's default message format.
    expect($errors[0])->toContain('Pending')->toContain('Shipped');
});

it('transitionEnumOrValidate() advances the property + dispatches on a valid transition', function (): void {
    $component = new OrderShowFixture;
    $component->status = OrderStatus::Pending;

    $ok = $component->transitionEnumOrValidate(
        'status',
        OrderStatus::Paid,
        messages: ['invalid' => 'never seen because the transition is valid'],
    );

    expect($ok)->toBeTrue();
    expect($component->status)->toBe(OrderStatus::Paid);
    expect($component->dispatched)->toHaveKey('enumerator.transitioned');
});
