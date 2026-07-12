<?php

declare(strict_types=1);

use Simtabi\Laranail\Enumerator\Presets\Enums\StatusEnum;
use Simtabi\Laranail\Enumerator\Support\CasesCollection;
use Simtabi\Laranail\Enumerator\Tests\Fixtures\Enums\BackedIntStatusEnum;
use Simtabi\Laranail\Enumerator\Tests\Fixtures\Enums\PureColorEnum;

// HasEnumeratorBehavior — collection helpers on every backed/pure enum.

it('all() is an alias for cases()', function (): void {
    expect(StatusEnum::all())->toBe(StatusEnum::cases());
});

it('values() returns backing values for backed enums', function (): void {
    expect(StatusEnum::values())->toBe(['active', 'inactive', 'pending', 'archived']);
});

it('values() returns names for pure enums', function (): void {
    expect(PureColorEnum::values())->toBe(['Red', 'Green', 'Blue']);
});

it('values() returns ints for int-backed enums', function (): void {
    expect(BackedIntStatusEnum::values())->toBe([1, 2, 3]);
});

it('names() returns the case names', function (): void {
    expect(StatusEnum::names())->toBe(['Active', 'Inactive', 'Pending', 'Archived']);
});

it('labels() returns [value => label]', function (): void {
    $labels = StatusEnum::labels();
    expect($labels['active'])->toBe('Active');
    expect($labels['archived'])->toBe('Archived');
});

it('options() returns [value => label] without a placeholder', function (): void {
    expect(StatusEnum::options())->toBe(StatusEnum::labels());
});

it('options() prepends an empty placeholder when provided', function (): void {
    $options = StatusEnum::options('Choose…');
    expect(array_key_first($options))->toBe('');
    expect($options[''])->toBe('Choose…');
});

it('count() returns the case count', function (): void {
    expect(StatusEnum::count())->toBe(4);
});

it('first() returns the first declared case', function (): void {
    expect(StatusEnum::first())->toBe(StatusEnum::Active);
});

it('last() returns the last declared case', function (): void {
    expect(StatusEnum::last())->toBe(StatusEnum::Archived);
});

it('random() returns one of the declared cases', function (): void {
    expect(StatusEnum::cases())->toContain(StatusEnum::random());
});

it('has() and isValid() recognise a valid backing value', function (): void {
    expect(StatusEnum::has('active'))->toBeTrue();
    expect(StatusEnum::isValid('active'))->toBeTrue();
});

it('has() returns false for unknown values', function (): void {
    expect(StatusEnum::has('nothing'))->toBeFalse();
});

it('doesntHave() is the inverse of has()', function (): void {
    expect(StatusEnum::doesntHave('active'))->toBeFalse();
    expect(StatusEnum::doesntHave('nothing'))->toBeTrue();
});

it('isPure() / isBacked() distinguish enum kinds', function (): void {
    expect(StatusEnum::isBacked())->toBeTrue();
    expect(StatusEnum::isPure())->toBeFalse();

    expect(PureColorEnum::isPure())->toBeTrue();
    expect(PureColorEnum::isBacked())->toBeFalse();
});

it('collect() returns a CasesCollection of every case', function (): void {
    $collection = StatusEnum::collect();
    expect($collection)->toBeInstanceOf(CasesCollection::class);
    expect($collection->count())->toBe(4);
});
