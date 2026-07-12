<?php

declare(strict_types=1);

use Simtabi\Laranail\Enumerator\Tests\Fixtures\Enums\GroupedStatusEnum;

// HasInvokableCases — `Status::Active()` static factory shorthand.

it('invokes a case as a static method', function (): void {
    expect(GroupedStatusEnum::Active())->toBe('active');
});

it('passes "label" as the first argument and returns the case label', function (): void {
    expect(GroupedStatusEnum::Active('label'))->toBe('Active');
});

it('returns the backing value with no arguments', function (): void {
    expect(GroupedStatusEnum::Banned())->toBe('banned');
});

it('throws BadMethodCallException for an unknown case', function (): void {
    GroupedStatusEnum::Bogus();
})->throws(BadMethodCallException::class);

it('returns the backing value when invoked with an unknown arg', function (): void {
    expect(GroupedStatusEnum::Active('unrecognised'))->toBe('active');
});
