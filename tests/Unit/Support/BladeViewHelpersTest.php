<?php

declare(strict_types=1);

use Simtabi\Laranail\Enumerator\Support\BladeViewHelpers;

// Direct coverage for BladeViewHelpers — buildGroups() is a thin delegator
// to CaseGroupingResolver (covered separately), so we sanity-check it
// here and exercise the full normalizeSelected() / toScalar() branch
// matrix that anonymous Blade variants depend on.

enum BvhBackedEnum: string
{
    case A = 'aa';
    case B = 'bb';
}

enum BvhIntEnum: int
{
    case Low = 1;
    case High = 2;
}

enum BvhPureEnum
{
    case Foo;
    case Bar;
}

final class BvhValueObject
{
    public function __construct(private string $value) {}

    public function getValue(): string
    {
        return $this->value;
    }
}

it('buildGroups() returns null when groupsBy is null', function (): void {
    expect(BladeViewHelpers::buildGroups([BvhBackedEnum::A], null))->toBeNull();
});

it('buildGroups() delegates to CaseGroupingResolver for closure form', function (): void {
    $out = BladeViewHelpers::buildGroups(
        [BvhBackedEnum::A, BvhBackedEnum::B],
        fn (BvhBackedEnum $c): string => $c === BvhBackedEnum::A ? 'first' : 'second',
    );
    expect($out)->toBe([
        'first' => [BvhBackedEnum::A],
        'second' => [BvhBackedEnum::B],
    ]);
});

it('normalizeSelected() returns null for null input', function (): void {
    expect(BladeViewHelpers::normalizeSelected(null))->toBeNull();
    expect(BladeViewHelpers::normalizeSelected(null, multiple: true))->toBeNull();
});

it('normalizeSelected() returns the backing value for a backed enum', function (): void {
    expect(BladeViewHelpers::normalizeSelected(BvhBackedEnum::A))->toBe('aa');
    expect(BladeViewHelpers::normalizeSelected(BvhIntEnum::Low))->toBe(1);
});

it('normalizeSelected() returns the case name for a pure enum', function (): void {
    expect(BladeViewHelpers::normalizeSelected(BvhPureEnum::Foo))->toBe('Foo');
});

it('normalizeSelected() returns the value-object getValue() for class-const-style objects', function (): void {
    expect(BladeViewHelpers::normalizeSelected(new BvhValueObject('xyz')))->toBe('xyz');
});

it('normalizeSelected() returns scalars unchanged when not multiple', function (): void {
    expect(BladeViewHelpers::normalizeSelected('plain'))->toBe('plain');
    expect(BladeViewHelpers::normalizeSelected(42))->toBe(42);
});

it('normalizeSelected() casts non-scalar non-enum non-getValue objects via (string) fallback', function (): void {
    $stringable = new class
    {
        public function __toString(): string
        {
            return 'casted';
        }
    };
    expect(BladeViewHelpers::normalizeSelected($stringable))->toBe('casted');
});

it('normalizeSelected() in multi-mode unwraps an iterable into a list of scalars', function (): void {
    $out = BladeViewHelpers::normalizeSelected(
        [BvhBackedEnum::A, BvhBackedEnum::B, BvhPureEnum::Foo, 'literal', 99],
        multiple: true,
    );
    expect($out)->toBe(['aa', 'bb', 'Foo', 'literal', 99]);
});

it('normalizeSelected() in multi-mode treats a single enum as a scalar — not iterated', function (): void {
    // UnitEnum is iterable-ish in PHP's sense (it's a class), but the
    // helper deliberately treats it as a single value even when multiple
    // is set so consumers can pass a single case to a multi-select.
    expect(BladeViewHelpers::normalizeSelected(BvhBackedEnum::A, multiple: true))->toBe('aa');
});

it('normalizeSelected() in multi-mode handles a Generator iterable', function (): void {
    $gen = (function (): Generator {
        yield BvhBackedEnum::A;
        yield 'tail';
    })();

    expect(BladeViewHelpers::normalizeSelected($gen, multiple: true))->toBe(['aa', 'tail']);
});
