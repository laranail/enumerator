<?php

declare(strict_types=1);

use Simtabi\Laranail\Enumerator\Exceptions\InvalidEnumeratorValueException;
use Simtabi\Laranail\Enumerator\Support\CasesCollection;
use Simtabi\Laranail\Enumerator\Tests\Fixtures\Enums\LegacyStatusEnum;

// HasClassEnumBehavior — the class-const path counterpart of
// HasEnumeratorBehavior. Exercises every public surface that's
// distinct from the native-enum trait.

// Construction

it('fromValue() returns an instance for a valid value', function (): void {
    $case = LegacyStatusEnum::fromValue('active');
    expect($case)->toBeInstanceOf(LegacyStatusEnum::class);
    expect($case->getValue())->toBe('active');
});

it('fromValue() throws for an unknown value', function (): void {
    LegacyStatusEnum::fromValue('nonexistent');
})->throws(InvalidEnumeratorValueException::class);

it('tryFromValue() returns null for unknown values', function (): void {
    expect(LegacyStatusEnum::tryFromValue('nonexistent'))->toBeNull();
});

it('fromKey() returns an instance for a valid key', function (): void {
    $case = LegacyStatusEnum::fromKey('ACTIVE');
    expect($case->getKey())->toBe('ACTIVE');
});

it('tryFromKey() returns null for unknown keys', function (): void {
    expect(LegacyStatusEnum::tryFromKey('NONEXISTENT'))->toBeNull();
});

it('__callStatic invokes a constant by name', function (): void {
    $case = LegacyStatusEnum::ACTIVE();
    expect($case->getValue())->toBe('active');
});

// Identity & equality

it('getValue() and getKey() return the constant value and name', function (): void {
    $case = LegacyStatusEnum::ACTIVE();
    expect($case->getValue())->toBe('active');
    expect($case->getKey())->toBe('ACTIVE');
});

it('__toString() returns the value', function (): void {
    expect((string) LegacyStatusEnum::ACTIVE())->toBe('active');
});

it('equals() compares to another instance', function (): void {
    expect(LegacyStatusEnum::ACTIVE()->equals(LegacyStatusEnum::ACTIVE()))->toBeTrue();
    expect(LegacyStatusEnum::ACTIVE()->equals(LegacyStatusEnum::INACTIVE()))->toBeFalse();
    expect(LegacyStatusEnum::ACTIVE()->equals(null))->toBeFalse();
});

it('is()/isNot()/in()/notIn() handle values and instances', function (): void {
    $case = LegacyStatusEnum::ACTIVE();
    expect($case->is('active'))->toBeTrue();
    expect($case->is(LegacyStatusEnum::ACTIVE()))->toBeTrue();
    expect($case->is('inactive'))->toBeFalse();

    expect($case->isNot('inactive'))->toBeTrue();

    expect($case->in(['active', 'banned']))->toBeTrue();
    expect($case->in(['inactive', 'banned']))->toBeFalse();

    expect($case->notIn(['inactive', 'banned']))->toBeTrue();
});

// Collection helpers

it('toArrayMap() returns [KEY => value]', function (): void {
    $map = LegacyStatusEnum::toArrayMap();
    expect($map['ACTIVE'])->toBe('active');
    expect($map['INACTIVE'])->toBe('inactive');
    expect($map['BANNED'])->toBe('banned');
});

it('keys() returns the constant names', function (): void {
    expect(LegacyStatusEnum::keys())->toBe(['ACTIVE', 'INACTIVE', 'BANNED']);
});

it('values() returns the constant values', function (): void {
    expect(LegacyStatusEnum::values())->toBe(['active', 'inactive', 'banned']);
});

it('cases() returns instances for each constant', function (): void {
    $cases = LegacyStatusEnum::cases();
    expect($cases)->toHaveCount(3);
    expect($cases[0])->toBeInstanceOf(LegacyStatusEnum::class);
});

it('collect() returns a CasesCollection', function (): void {
    expect(LegacyStatusEnum::collect())->toBeInstanceOf(CasesCollection::class);
});

it('count() returns the constant count', function (): void {
    expect(LegacyStatusEnum::count())->toBe(3);
});

it('random() returns one of the cases', function (): void {
    $cases = LegacyStatusEnum::cases();
    $values = array_map(fn ($c) => $c->getValue(), $cases);
    expect($values)->toContain(LegacyStatusEnum::random()->getValue());
});

it('hasCase() reflects key membership', function (): void {
    expect(LegacyStatusEnum::hasCase('ACTIVE'))->toBeTrue();
    expect(LegacyStatusEnum::hasCase('NONEXISTENT'))->toBeFalse();
});

it('isValid() reflects value membership', function (): void {
    expect(LegacyStatusEnum::isValid('active'))->toBeTrue();
    expect(LegacyStatusEnum::isValid('nope'))->toBeFalse();
});

// Presentation

it('labels() returns [value => label]', function (): void {
    $labels = LegacyStatusEnum::labels();
    expect($labels['active'])->toBe('Active');
    expect($labels['inactive'])->toBe('Inactive');
});

it('options() includes an empty placeholder when given one', function (): void {
    $options = LegacyStatusEnum::options('Choose…');
    expect(array_key_first($options))->toBe('');
    expect($options[''])->toBe('Choose…');
});

it('label() / color() / icon() resolve attributes on a case', function (): void {
    $case = LegacyStatusEnum::ACTIVE();
    expect($case->label())->toBe('Active');
    expect($case->color())->toBe('success');
    expect($case->icon())->toBeNull();
});

it('description() returns null when no #[Description] is set', function (): void {
    expect(LegacyStatusEnum::ACTIVE()->description())->toBeNull();
});

it('help() returns null when no #[Help] is set', function (): void {
    expect(LegacyStatusEnum::ACTIVE()->help())->toBeNull();
});

it('order() returns null when no #[Order] is set', function (): void {
    expect(LegacyStatusEnum::ACTIVE()->order())->toBeNull();
});

it('meta() without a key returns the whole meta array (empty by default)', function (): void {
    expect(LegacyStatusEnum::ACTIVE()->meta())->toBe([]);
});

it('meta(key) returns null for an undeclared key', function (): void {
    expect(LegacyStatusEnum::ACTIVE()->meta('priority'))->toBeNull();
});

it('toArray() returns the value/key/label shape for class-const enums', function (): void {
    $case = LegacyStatusEnum::ACTIVE();
    $arr = $case->toArray();
    expect($arr)->toHaveKey('value')->toHaveKey('key')->toHaveKey('label');
    expect($arr['value'])->toBe('active');
    expect($arr['key'])->toBe('ACTIVE');
    expect($arr['label'])->toBe('Active');
});

it('jsonSerialize() matches toArray()', function (): void {
    $case = LegacyStatusEnum::ACTIVE();
    expect($case->jsonSerialize())->toBe($case->toArray());
});

it('toJson() emits parseable JSON', function (): void {
    $case = LegacyStatusEnum::ACTIVE();
    $decoded = json_decode($case->toJson(), true);
    expect($decoded)->toBeArray()
        ->toHaveKey('value')
        ->toHaveKey('key')
        ->toHaveKey('label');
});

it('toHtml() renders an HtmlString containing the case label', function (): void {
    $html = (string) LegacyStatusEnum::ACTIVE()->toHtml();
    expect($html)->toContain('Active');
    expect($html)->toContain('role="status"');
});

it('toHtml() falls back to enumerator-{color} when no #[CssClass] is set', function (): void {
    // LegacyStatusEnum has no #[CssClass] attribute, so toHtml() uses
    // the `enumerator-badge enumerator-{color}` fallback.
    $html = (string) LegacyStatusEnum::ACTIVE()->toHtml();
    expect($html)->toContain('enumerator-success');
});

it('cssClass() returns null when no #[CssClass] is set', function (): void {
    expect(LegacyStatusEnum::ACTIVE()->cssClass('bootstrap'))->toBeNull();
});
