<?php

declare(strict_types=1);

use Simtabi\Laranail\Enumerator\Tests\Fixtures\Enums\GroupedStatusEnum;

// HasGrouping — groups()/inGroup()/group()/predicate sugar.

it('groups() returns the declared map', function (): void {
    $groups = GroupedStatusEnum::groups();
    expect($groups)->toHaveKey('positive')
        ->toHaveKey('pending')
        ->toHaveKey('negative');
});

it('inGroup() returns true for a case in the group', function (): void {
    expect(GroupedStatusEnum::Active->inGroup('positive'))->toBeTrue();
    expect(GroupedStatusEnum::Pending->inGroup('pending'))->toBeTrue();
    expect(GroupedStatusEnum::Banned->inGroup('negative'))->toBeTrue();
});

it('inGroup() returns false for a case not in the group', function (): void {
    expect(GroupedStatusEnum::Active->inGroup('negative'))->toBeFalse();
});

it('inGroup() returns false for an unknown group name', function (): void {
    expect(GroupedStatusEnum::Active->inGroup('nonexistent'))->toBeFalse();
});

it('group() returns the array of cases for the named group', function (): void {
    $cases = GroupedStatusEnum::group('positive');
    expect($cases)->toContain(GroupedStatusEnum::Active);
    expect($cases)->toContain(GroupedStatusEnum::Approved);
});

it('group() returns an empty array for unknown names', function (): void {
    expect(GroupedStatusEnum::group('bogus'))->toBe([]);
});

it('isPositive() / isNegative() / isPending() reflect group membership', function (): void {
    expect(GroupedStatusEnum::Active->isPositive())->toBeTrue();
    expect(GroupedStatusEnum::Active->isNegative())->toBeFalse();
    expect(GroupedStatusEnum::Active->isPending())->toBeFalse();

    expect(GroupedStatusEnum::Pending->isPending())->toBeTrue();
    expect(GroupedStatusEnum::Pending->isPositive())->toBeFalse();

    expect(GroupedStatusEnum::Banned->isNegative())->toBeTrue();
    expect(GroupedStatusEnum::Banned->isPositive())->toBeFalse();
});
