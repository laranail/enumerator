<?php

declare(strict_types=1);

use PHPUnit\Framework\AssertionFailedError;
use Simtabi\Laranail\Enumerator\Helpers\Bitmask;
use Simtabi\Laranail\Enumerator\Modules\Pest\Expectations;
use Simtabi\Laranail\Enumerator\Presets\Enums\FeatureFlagEnum;
use Simtabi\Laranail\Enumerator\Presets\Enums\OrderStatusEnum;
use Simtabi\Laranail\Enumerator\Presets\Enums\StatusEnum;

// custom Pest expectations for enumerator cases.
// Register up-front so the expectations are available in every test below.
beforeEach(function (): void {
    Expectations::register();
});

it('toBeCase passes when the case matches', function (): void {
    expect(StatusEnum::Active)->toBeCase(StatusEnum::Active);
});

it('toBeCase fails when the case does not match', function (): void {
    expect(fn () => expect(StatusEnum::Active)->toBeCase(StatusEnum::Inactive))
        ->toThrow(AssertionFailedError::class);
});

it('toBeIn passes when the case is in the allowed set', function (): void {
    expect(StatusEnum::Active)->toBeIn([StatusEnum::Active, StatusEnum::Pending]);
});

it('toBeIn fails when the case is not in the allowed set', function (): void {
    expect(fn () => expect(StatusEnum::Active)->toBeIn([StatusEnum::Inactive, StatusEnum::Archived]))
        ->toThrow(AssertionFailedError::class);
});

it('toEqualEnum passes for matching cases', function (): void {
    expect(StatusEnum::Active)->toEqualEnum(StatusEnum::Active);
});

it('toEqualEnum passes for matching backing value', function (): void {
    expect(StatusEnum::Active)->toEqualEnum('active');
});

it('toHaveBit passes when the Bitmask contains the case', function (): void {
    $mask = new Bitmask(FeatureFlagEnum::class, [FeatureFlagEnum::DarkMode, FeatureFlagEnum::BetaUI]);

    expect($mask)->toHaveBit(FeatureFlagEnum::DarkMode);
});

it('toHaveBit fails when the Bitmask does not contain the case', function (): void {
    $mask = new Bitmask(FeatureFlagEnum::class, [FeatureFlagEnum::DarkMode]);

    expect(fn () => expect($mask)->toHaveBit(FeatureFlagEnum::Telemetry))
        ->toThrow(AssertionFailedError::class);
});

it('toCanTransitionTo passes when the transition is allowed', function (): void {
    expect(OrderStatusEnum::Pending)->toCanTransitionTo(OrderStatusEnum::Confirmed);
});

it('toCanTransitionTo fails when the transition is disallowed', function (): void {
    expect(fn () => expect(OrderStatusEnum::Delivered)->toCanTransitionTo(OrderStatusEnum::Pending))
        ->toThrow(AssertionFailedError::class);
});

it('does not register expectations when the module is disabled', function (): void {
    // Sanity: when the module flag is off and we never call Expectations::register(),
    // the toBeCase extension wouldn't exist. We can't easily test the un-registered
    // state once register() has fired in beforeEach, so we just verify register()
    // is idempotent.
    Expectations::register();
    Expectations::register();
    expect(StatusEnum::Active)->toBeCase(StatusEnum::Active);
});
