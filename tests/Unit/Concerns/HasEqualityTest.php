<?php

declare(strict_types=1);

use Simtabi\Laranail\Enumerator\Presets\Enums\StatusEnum;
use Simtabi\Laranail\Enumerator\Tests\Fixtures\Enums\BackedIntStatusEnum;
use Simtabi\Laranail\Enumerator\Tests\Fixtures\Enums\PureColorEnum;

// HasEquality — `is`, `isNot`, `in`, `notIn`, `equals`.

it('is() returns true for the same case', function (): void {
    expect(StatusEnum::Active->is(StatusEnum::Active))->toBeTrue();
});

it('is() returns true for a matching backing value (string enum)', function (): void {
    expect(StatusEnum::Active->is('active'))->toBeTrue();
});

it('is() returns true for a matching backing value (int enum)', function (): void {
    expect(BackedIntStatusEnum::Active->is(1))->toBeTrue();
});

it('is() returns true for a matching case name on a pure enum', function (): void {
    expect(PureColorEnum::Red->is('Red'))->toBeTrue();
});

it('is() returns false for a non-matching case', function (): void {
    expect(StatusEnum::Active->is(StatusEnum::Archived))->toBeFalse();
});

it('is() returns false for null', function (): void {
    expect(StatusEnum::Active->is(null))->toBeFalse();
});

it('isNot() is the boolean inverse of is()', function (): void {
    expect(StatusEnum::Active->isNot(StatusEnum::Archived))->toBeTrue();
    expect(StatusEnum::Active->isNot(StatusEnum::Active))->toBeFalse();
});

it('in() returns true when the case is among an iterable of cases', function (): void {
    expect(StatusEnum::Active->in([StatusEnum::Active, StatusEnum::Pending]))->toBeTrue();
});

it('in() returns true when the case matches by backing value', function (): void {
    expect(StatusEnum::Active->in(['active', 'pending']))->toBeTrue();
});

it('in() returns false when the case is absent', function (): void {
    expect(StatusEnum::Active->in([StatusEnum::Pending, StatusEnum::Archived]))->toBeFalse();
});

it('notIn() is the inverse of in()', function (): void {
    expect(StatusEnum::Active->notIn([StatusEnum::Pending]))->toBeTrue();
    expect(StatusEnum::Active->notIn([StatusEnum::Active]))->toBeFalse();
});

it('equals() returns true only for identical UnitEnum instances', function (): void {
    expect(StatusEnum::Active->equals(StatusEnum::Active))->toBeTrue();
    expect(StatusEnum::Active->equals(StatusEnum::Archived))->toBeFalse();
    expect(StatusEnum::Active->equals(null))->toBeFalse();
});

it('equals() returns false when comparing across enum types with the same value', function (): void {
    expect(StatusEnum::Active->equals(PureColorEnum::Red))->toBeFalse();
});
