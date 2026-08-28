<?php

declare(strict_types=1);

use Simtabi\Laranail\Enumerator\Support\CasesCollection;
use Simtabi\Laranail\Enumerator\Tests\Fixtures\Enums\OrderedStatusEnum;
use Simtabi\Laranail\Enumerator\Tests\Fixtures\Enums\NullOrderReportingEnum;

// HasOrder — explicit ordering + sortedBy* + compare helpers.

it('getOrder() returns the #[Order] attribute value', function (): void {
    expect(OrderedStatusEnum::First->getOrder())->toBe(10);
    expect(OrderedStatusEnum::Second->getOrder())->toBe(20);
    expect(OrderedStatusEnum::Third->getOrder())->toBe(30);
    expect(OrderedStatusEnum::Fourth->getOrder())->toBe(40);
});

it('compareTo() returns -1/0/+1 by declared order', function (): void {
    expect(OrderedStatusEnum::First->compareTo(OrderedStatusEnum::Second))->toBe(-1);
    expect(OrderedStatusEnum::Second->compareTo(OrderedStatusEnum::First))->toBe(1);
    expect(OrderedStatusEnum::First->compareTo(OrderedStatusEnum::First))->toBe(0);
});

it('isHigherThan() / isLowerThan() respect the order', function (): void {
    expect(OrderedStatusEnum::Third->isHigherThan(OrderedStatusEnum::Second))->toBeTrue();
    expect(OrderedStatusEnum::Second->isLowerThan(OrderedStatusEnum::Third))->toBeTrue();
    expect(OrderedStatusEnum::Second->isHigherThan(OrderedStatusEnum::Third))->toBeFalse();
});

it('isHigherOrEqual() / isLowerOrEqual() accept equality', function (): void {
    expect(OrderedStatusEnum::Second->isHigherOrEqual(OrderedStatusEnum::Second))->toBeTrue();
    expect(OrderedStatusEnum::Second->isLowerOrEqual(OrderedStatusEnum::Second))->toBeTrue();
    expect(OrderedStatusEnum::First->isHigherOrEqual(OrderedStatusEnum::Second))->toBeFalse();
});

it('sortedByOrder() returns a CasesCollection in ascending #[Order]', function (): void {
    $sorted = OrderedStatusEnum::sortedByOrder();
    expect($sorted)->toBeInstanceOf(CasesCollection::class);
    expect($sorted->flatValues())->toBe(['first', 'second', 'third', 'fourth']);
});

it('sortedByOrderDesc() returns the reverse order', function (): void {
    $sorted = OrderedStatusEnum::sortedByOrderDesc();
    expect($sorted->flatValues())->toBe(['fourth', 'third', 'second', 'first']);
});

it('falls back to the attribute when a foreign getOrder() cannot answer', function (): void {
    // compareTo() probes with method_exists(), which proves the method is
    // callable and nothing about its return. The old code cast it — and
    // (int) null is 0, so a case reporting null sorted before everything,
    // inverting the documented default that an unordered case sorts last.
    expect(OrderedStatusEnum::First->compareTo(NullOrderReportingEnum::Early))->toBe(0);
    expect(OrderedStatusEnum::First->compareTo(NullOrderReportingEnum::Late))->toBe(-1);
    expect(OrderedStatusEnum::Fourth->compareTo(NullOrderReportingEnum::Early))->toBe(1);
});
