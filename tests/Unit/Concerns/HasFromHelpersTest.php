<?php

declare(strict_types=1);

use Simtabi\Laranail\Enumerator\Exceptions\InvalidEnumeratorNameException;
use Simtabi\Laranail\Enumerator\Exceptions\InvalidEnumeratorValueException;
use Simtabi\Laranail\Enumerator\Presets\Enums\StatusEnum;
use Simtabi\Laranail\Enumerator\Support\AttributesCache;
use Simtabi\Laranail\Enumerator\Tests\Fixtures\Enums\BackedIntStatusEnum;
use Simtabi\Laranail\Enumerator\Tests\Fixtures\Enums\PureColorEnum;

// HasFromHelpers — fromName/tryFromName/fromMeta/coerce/fromAny.

it('fromName() returns the case with a matching name', function (): void {
    expect(StatusEnum::fromName('Active'))->toBe(StatusEnum::Active);
});

it('fromName() throws on an unknown name', function (): void {
    StatusEnum::fromName('Bogus');
})->throws(InvalidEnumeratorNameException::class);

it('tryFromName() returns the case or null', function (): void {
    expect(StatusEnum::tryFromName('Active'))->toBe(StatusEnum::Active);
    expect(StatusEnum::tryFromName('Bogus'))->toBeNull();
});

it('tryFromName() works on pure enums', function (): void {
    expect(PureColorEnum::tryFromName('Red'))->toBe(PureColorEnum::Red);
    expect(PureColorEnum::tryFromName('Magenta'))->toBeNull();
});

it('coerce() resolves backing value, name, or null', function (): void {
    expect(StatusEnum::coerce('active'))->toBe(StatusEnum::Active);
    expect(StatusEnum::coerce('Active'))->toBe(StatusEnum::Active);
    expect(StatusEnum::coerce('nothing'))->toBeNull();
    expect(StatusEnum::coerce(null))->toBeNull();
});

it('coerce() handles int backing values', function (): void {
    expect(BackedIntStatusEnum::coerce(1))->toBe(BackedIntStatusEnum::Active);
});

it('fromAny() resolves like coerce() but throws on miss', function (): void {
    expect(StatusEnum::fromAny('active'))->toBe(StatusEnum::Active);
    expect(StatusEnum::fromAny('Active'))->toBe(StatusEnum::Active);

    expect(fn (): mixed => StatusEnum::fromAny('nothing'))
        ->toThrow(InvalidEnumeratorValueException::class);
});

it('fromMeta() throws ValueError when nothing matches', function (): void {
    StatusEnum::fromMeta('anything');
})->throws(ValueError::class);

it('tryFromMeta() returns null when nothing matches', function (): void {
    expect(StatusEnum::tryFromMeta('nothing'))->toBeNull();
});

it('fromMeta() returns the case collection when matches are found', function (): void {
    AttributesCache::flush();
    config()->set('enumerator.overrides', [
        StatusEnum::class => [
            'Active' => ['meta' => ['priority' => 'high']],
            'Pending' => ['meta' => ['priority' => 'high']],
        ],
    ]);

    $collection = StatusEnum::fromMeta('priority', 'high');
    expect($collection->count())->toBe(2);
});

it('tryFromMeta() returns a collection when matches are found', function (): void {
    AttributesCache::flush();
    config()->set('enumerator.overrides', [
        StatusEnum::class => [
            'Active' => ['meta' => ['featured' => true]],
        ],
    ]);

    $collection = StatusEnum::tryFromMeta('featured', true);
    expect($collection)->not->toBeNull();
    expect($collection->count())->toBe(1);
});

it('tryFromMeta() supports a callable predicate', function (): void {
    AttributesCache::flush();
    config()->set('enumerator.overrides', [
        StatusEnum::class => [
            'Active' => ['meta' => ['priority' => 5]],
            'Pending' => ['meta' => ['priority' => 10]],
        ],
    ]);

    $collection = StatusEnum::tryFromMeta('priority', fn ($v) => is_int($v) && $v >= 5);
    expect($collection?->count())->toBe(2);
});

it('coerce() returns null when an int is given to an int-backed enum and no value matches', function (): void {
    expect(BackedIntStatusEnum::coerce(9999))->toBeNull();
});
