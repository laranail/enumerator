<?php

declare(strict_types=1);

use Simtabi\Laranail\Enumerator\Presets\Enums\StatusEnum;
use Simtabi\Laranail\Enumerator\Support\IsEnumeratorClass;
use Simtabi\Laranail\Enumerator\Tests\Fixtures\Enums\BackedIntStatusEnum;
use Simtabi\Laranail\Enumerator\Tests\Fixtures\Enums\LegacyStatusEnum;
use Simtabi\Laranail\Enumerator\Tests\Fixtures\Enums\PureColorEnum;

it('check() returns true for native string-backed enums', function (): void {
    expect(IsEnumeratorClass::check(StatusEnum::class))->toBeTrue();
});

it('check() returns true for native int-backed enums', function (): void {
    expect(IsEnumeratorClass::check(BackedIntStatusEnum::class))->toBeTrue();
});

it('check() returns true for pure enums', function (): void {
    expect(IsEnumeratorClass::check(PureColorEnum::class))->toBeTrue();
});

it('check() returns true for AbstractEnumeratorClass subclasses', function (): void {
    expect(IsEnumeratorClass::check(LegacyStatusEnum::class))->toBeTrue();
});

it('check() returns false for non-enumerator classes', function (): void {
    expect(IsEnumeratorClass::check(stdClass::class))->toBeFalse();
    expect(IsEnumeratorClass::check('Nonexistent\\Class'))->toBeFalse();
});

it('casesOf() yields all cases for native enums', function (): void {
    $cases = [];
    foreach (IsEnumeratorClass::casesOf(StatusEnum::class) as $case) {
        $cases[] = $case;
    }
    expect($cases)->toHaveCount(4);  // StatusEnum has 4 cases
});

it('casesOf() yields all cases for class-const enums', function (): void {
    $cases = [];
    foreach (IsEnumeratorClass::casesOf(LegacyStatusEnum::class) as $case) {
        $cases[] = $case;
    }
    expect($cases)->toHaveCount(3);  // LegacyStatusEnum has 3 cases
});

it('casesOf() yields nothing for non-enumerator classes', function (): void {
    $cases = [];
    foreach (IsEnumeratorClass::casesOf(stdClass::class) as $case) {
        $cases[] = $case;
    }
    expect($cases)->toBe([]);
});

it('valueOf() returns the backing value for backed enums', function (): void {
    expect(IsEnumeratorClass::valueOf(StatusEnum::Active))->toBe('active');
    expect(IsEnumeratorClass::valueOf(BackedIntStatusEnum::Active))->toBe(1);
});

it('valueOf() returns the name for pure enums', function (): void {
    expect(IsEnumeratorClass::valueOf(PureColorEnum::Red))->toBe('Red');
});

it('valueOf() returns getValue() for class-const cases', function (): void {
    expect(IsEnumeratorClass::valueOf(LegacyStatusEnum::ACTIVE()))->toBe('active');
});

it('valueOf() throws on unsupported objects', function (): void {
    IsEnumeratorClass::valueOf(new stdClass);
})->throws(InvalidArgumentException::class);
