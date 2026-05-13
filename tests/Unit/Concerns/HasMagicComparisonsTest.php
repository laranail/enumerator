<?php

declare(strict_types=1);

use Simtabi\Laranail\Enumerator\Exceptions\AmbiguousMagicCallException;
use Simtabi\Laranail\Enumerator\Tests\Fixtures\Enums\GroupedStatusEnum;

// HasMagicComparisons — `$case->isFoo()` / `isNotFoo()` magic predicates,
// plus isOneOf / isNoneOf helpers. GroupedStatusEnum uses HasEnumerator
// umbrella (includes HasMagicComparisons + ResolvesMagicCalls).

it('isOneOf() returns true for a member case', function (): void {
    expect(GroupedStatusEnum::Active->isOneOf(GroupedStatusEnum::Active, GroupedStatusEnum::Pending))
        ->toBeTrue();
});

it('isOneOf() returns false otherwise', function (): void {
    expect(GroupedStatusEnum::Active->isOneOf(GroupedStatusEnum::Pending, GroupedStatusEnum::Banned))
        ->toBeFalse();
});

it('isNoneOf() is the inverse of isOneOf()', function (): void {
    expect(GroupedStatusEnum::Active->isNoneOf(GroupedStatusEnum::Pending))->toBeTrue();
    expect(GroupedStatusEnum::Active->isNoneOf(GroupedStatusEnum::Active))->toBeFalse();
});

// Magic dispatch via __call → magicCompare

it('isCase() returns true for matching case', function (): void {
    /** @var bool $result */
    $result = GroupedStatusEnum::Active->isActive();
    expect($result)->toBeTrue();
});

it('isCase() returns false for a different case', function (): void {
    expect(GroupedStatusEnum::Active->isBanned())->toBeFalse();
});

it('isNotCase() returns the inverse', function (): void {
    expect(GroupedStatusEnum::Active->isNotBanned())->toBeTrue();
    expect(GroupedStatusEnum::Active->isNotActive())->toBeFalse();
});

it('case-insensitive case-name resolution when the config flag is on (default)', function (): void {
    // The "is" prefix itself must be lowercase to enter magicCompare; the
    // case-name remainder is matched case-insensitively.
    config()->set('enumerator.magic.case_insensitive_method_names', true);
    expect(GroupedStatusEnum::Active->isactive())->toBeTrue();
    expect(GroupedStatusEnum::Active->isACTIVE())->toBeTrue();
});

it('case-insensitive resolution is disabled when the config flag is off', function (): void {
    config()->set('enumerator.magic.case_insensitive_method_names', false);
    expect(fn () => GroupedStatusEnum::Active->isactive())
        ->toThrow(BadMethodCallException::class);
});

it('isCase() throws BadMethodCallException for unknown case', function (): void {
    GroupedStatusEnum::Active->isBogus();
})->throws(BadMethodCallException::class);

// Ambiguous-resolution config branches

it('non-ambiguous lookup just works (exact match)', function (): void {
    expect(GroupedStatusEnum::Active->isApproved())->toBeFalse();
    expect(GroupedStatusEnum::Approved->isApproved())->toBeTrue();
});

it('AmbiguousMagicCallException constructor is importable', function (): void {
    $e = new AmbiguousMagicCallException('test message');
    expect($e->getMessage())->toBe('test message');
});
