<?php

declare(strict_types=1);

use Simtabi\Laranail\Enumerator\Support\CaseGroupingResolver;
use Simtabi\Laranail\Enumerator\Tests\Fixtures\Enums\GroupedStatusEnum;

// CaseGroupingResolver — five-strategy grouping helper used by Blade.

it('returns null when groupsBy is null', function (): void {
    expect(CaseGroupingResolver::resolve(GroupedStatusEnum::cases(), null))->toBeNull();
});

it('returns an explicit map as-is', function (): void {
    $explicit = [
        'left' => [GroupedStatusEnum::Active],
        'right' => [GroupedStatusEnum::Banned],
    ];
    expect(CaseGroupingResolver::resolve(GroupedStatusEnum::cases(), $explicit))
        ->toBe($explicit);
});

it('returns the enum groups() map when groupsBy is "groups"', function (): void {
    $resolved = CaseGroupingResolver::resolve(GroupedStatusEnum::cases(), 'groups');
    expect($resolved)->toHaveKey('positive');
    expect($resolved)->toHaveKey('negative');
    expect($resolved)->toHaveKey('pending');
});

it('returns null when groupsBy is "groups" but the enum has no groups() method', function (): void {
    $resolved = CaseGroupingResolver::resolve([], 'groups');
    expect($resolved)->toBeNull();
});

it('groups by a Closure key function', function (): void {
    $resolved = CaseGroupingResolver::resolve(
        GroupedStatusEnum::cases(),
        fn (object $c): string => str_starts_with($c->name, 'A') ? 'a-start' : 'other',
    );
    expect($resolved)->toHaveKey('a-start');
    expect($resolved)->toHaveKey('other');
});

it('relabels groups via the $groupLabels map', function (): void {
    $resolved = CaseGroupingResolver::resolve(
        GroupedStatusEnum::cases(),
        'groups',
        ['positive' => 'Healthy', 'negative' => 'Blocked', 'pending' => 'Hold'],
    );
    expect($resolved)->toHaveKey('Healthy');
    expect($resolved)->toHaveKey('Blocked');
    expect($resolved)->toHaveKey('Hold');
    expect($resolved)->not->toHaveKey('positive');
});

it('returns null for unsupported groupsBy strings', function (): void {
    expect(CaseGroupingResolver::resolve(GroupedStatusEnum::cases(), 'something:weird'))
        ->toBeNull();
});

it('groups by attribute accessor via "attribute:<name>"', function (): void {
    // Group by the each case's label() accessor (so each label is its own group).
    $resolved = CaseGroupingResolver::resolve(
        GroupedStatusEnum::cases(),
        'attribute:label',
    );
    expect($resolved)->toHaveKey('Active');
    expect($resolved)->toHaveKey('Approved');
});
