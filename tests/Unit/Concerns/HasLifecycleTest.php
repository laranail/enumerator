<?php

declare(strict_types=1);

use Simtabi\Laranail\Enumerator\Tests\Fixtures\Enums\GroupedStatusEnum;

// HasLifecycle — next()/previous()/isFirst()/isLast().
// GroupedStatusEnum uses the HasEnumerator umbrella (includes HasLifecycle).
// Declaration order: Active, Approved, Pending, Review, Rejected, Banned.

it('next() returns the next case in declaration order', function (): void {
    expect(GroupedStatusEnum::Active->next())->toBe(GroupedStatusEnum::Approved);
    expect(GroupedStatusEnum::Approved->next())->toBe(GroupedStatusEnum::Pending);
});

it('next() returns null on the last case', function (): void {
    expect(GroupedStatusEnum::Banned->next())->toBeNull();
});

it('previous() returns the previous case in declaration order', function (): void {
    expect(GroupedStatusEnum::Approved->previous())->toBe(GroupedStatusEnum::Active);
    expect(GroupedStatusEnum::Pending->previous())->toBe(GroupedStatusEnum::Approved);
});

it('previous() returns null on the first case', function (): void {
    expect(GroupedStatusEnum::Active->previous())->toBeNull();
});

it('isFirst() returns true only for the first declared case', function (): void {
    expect(GroupedStatusEnum::Active->isFirst())->toBeTrue();
    expect(GroupedStatusEnum::Approved->isFirst())->toBeFalse();
    expect(GroupedStatusEnum::Banned->isFirst())->toBeFalse();
});

it('isLast() returns true only for the last declared case', function (): void {
    expect(GroupedStatusEnum::Banned->isLast())->toBeTrue();
    expect(GroupedStatusEnum::Active->isLast())->toBeFalse();
});
