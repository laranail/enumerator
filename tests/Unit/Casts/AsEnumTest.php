<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Model;
use Simtabi\Laranail\Enumerator\Casts\AsEnum;
use Simtabi\Laranail\Enumerator\Exceptions\InvalidEnumeratorValueException;
use Simtabi\Laranail\Enumerator\Presets\Enums\StatusEnum;
use Simtabi\Laranail\Enumerator\Tests\Fixtures\Enums\BackedIntStatusEnum;
use Simtabi\Laranail\Enumerator\Tests\Fixtures\Enums\LegacyStatusEnum;
use Simtabi\Laranail\Enumerator\Tests\Fixtures\Enums\PureColorEnum;

// AsEnum — Eloquent cast for native and class-const enums.

function asEnumTestModel(): Model
{
    return new class extends Model {};
}

it('of() returns the cast spec string with the enum FQCN', function (): void {
    expect(AsEnum::of(StatusEnum::class))
        ->toBe(AsEnum::class . ':' . StatusEnum::class);
});

it('constructor rejects classes that are not Enumerator', function (): void {
    new AsEnum(stdClass::class);
})->throws(InvalidArgumentException::class);

it('get() returns null when the stored value is null', function (): void {
    $cast = new AsEnum(StatusEnum::class);
    expect($cast->get(asEnumTestModel(), 'status', null, []))->toBeNull();
});

it('get() passes through an already-hydrated case', function (): void {
    $cast = new AsEnum(StatusEnum::class);
    expect($cast->get(asEnumTestModel(), 'status', StatusEnum::Active, []))
        ->toBe(StatusEnum::Active);
});

it('get() hydrates a string backing value', function (): void {
    $cast = new AsEnum(StatusEnum::class);
    expect($cast->get(asEnumTestModel(), 'status', 'active', []))
        ->toBe(StatusEnum::Active);
});

it('get() hydrates an int backing value for int-backed enums', function (): void {
    $cast = new AsEnum(BackedIntStatusEnum::class);
    expect($cast->get(asEnumTestModel(), 'status', 1, []))
        ->toBe(BackedIntStatusEnum::Active);
});

it('get() falls back to tryFromName() when value miss but name hits', function (): void {
    $cast = new AsEnum(StatusEnum::class);
    // Case name (not value) — should resolve via the name fallback.
    expect($cast->get(asEnumTestModel(), 'status', 'Active', []))
        ->toBe(StatusEnum::Active);
});

it('get() resolves pure enums by name', function (): void {
    $cast = new AsEnum(PureColorEnum::class);
    expect($cast->get(asEnumTestModel(), 'color', 'Red', []))
        ->toBe(PureColorEnum::Red);
});

it('get() throws InvalidEnumeratorValueException on an unrecognised value', function (): void {
    $cast = new AsEnum(StatusEnum::class);
    $cast->get(asEnumTestModel(), 'status', 'nothing', []);
})->throws(InvalidEnumeratorValueException::class);

it('get() hydrates an AbstractEnumeratorClass subclass via fromValue', function (): void {
    $cast = new AsEnum(LegacyStatusEnum::class);
    $hydrated = $cast->get(asEnumTestModel(), 'status', 'active', []);
    expect($hydrated)->toBeInstanceOf(LegacyStatusEnum::class);
    expect($hydrated->getValue())->toBe('active');
});

it('set() returns null for a null value', function (): void {
    $cast = new AsEnum(StatusEnum::class);
    expect($cast->set(asEnumTestModel(), 'status', null, []))->toBeNull();
});

it('set() persists a BackedEnum value to its backing value', function (): void {
    $cast = new AsEnum(StatusEnum::class);
    expect($cast->set(asEnumTestModel(), 'status', StatusEnum::Active, []))
        ->toBe('active');
});

it('set() persists a pure UnitEnum value to its name', function (): void {
    $cast = new AsEnum(PureColorEnum::class);
    expect($cast->set(asEnumTestModel(), 'color', PureColorEnum::Red, []))
        ->toBe('Red');
});

it('set() persists an AbstractEnumeratorClass instance via getValue', function (): void {
    $cast = new AsEnum(LegacyStatusEnum::class);
    $instance = LegacyStatusEnum::fromValue('active');
    expect($cast->set(asEnumTestModel(), 'status', $instance, []))
        ->toBe('active');
});

it('set() validates and persists a raw string value', function (): void {
    $cast = new AsEnum(StatusEnum::class);
    expect($cast->set(asEnumTestModel(), 'status', 'active', []))
        ->toBe('active');
});

it('set() throws on an invalid raw string', function (): void {
    $cast = new AsEnum(StatusEnum::class);
    $cast->set(asEnumTestModel(), 'status', 'nothing', []);
})->throws(InvalidEnumeratorValueException::class);

it('set() throws on an unsupported type', function (): void {
    $cast = new AsEnum(StatusEnum::class);
    $cast->set(asEnumTestModel(), 'status', new stdClass, []);
})->throws(InvalidEnumeratorValueException::class);

it('get() throws when the stored value is not string or int (e.g. an array)', function (): void {
    $cast = new AsEnum(StatusEnum::class);
    $cast->get(asEnumTestModel(), 'status', ['nope'], []);
})->throws(InvalidEnumeratorValueException::class);

it('get() throws on a pure-enum miss with an int value', function (): void {
    $cast = new AsEnum(PureColorEnum::class);
    // Pure enums only resolve by name; passing an int that can't match a
    // case name should throw.
    $cast->get(asEnumTestModel(), 'color', 42, []);
})->throws(InvalidEnumeratorValueException::class);

it('get() throws on a pure-enum miss with an unrecognised name', function (): void {
    $cast = new AsEnum(PureColorEnum::class);
    $cast->get(asEnumTestModel(), 'color', 'Magenta', []);
})->throws(InvalidEnumeratorValueException::class);
