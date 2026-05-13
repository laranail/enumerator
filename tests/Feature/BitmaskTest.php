<?php

declare(strict_types=1);

use Simtabi\Laranail\Enumerator\Presets\Enums\BasicPermissionEnum;
use Simtabi\Laranail\Enumerator\Presets\Enums\FeatureFlagEnum;
use Simtabi\Laranail\Enumerator\Presets\Enums\NotificationOptInEnum;

it('builds bitmasks from int-backed enums', function (): void {
    $mask = BasicPermissionEnum::mask(BasicPermissionEnum::Read, BasicPermissionEnum::Admin);
    expect($mask->toInt())->toBe(9);
    expect($mask->has(BasicPermissionEnum::Read))->toBeTrue();
    expect($mask->has(BasicPermissionEnum::Write))->toBeFalse();
});

it('builds bitmasks from string-backed enums', function (): void {
    $mask = FeatureFlagEnum::mask(FeatureFlagEnum::DarkMode, FeatureFlagEnum::Telemetry);
    expect($mask->toInt())->toBe(9);
    expect($mask->values())->toBe(['dark_mode', 'telemetry']);
});

it('builds bitmasks from pure enums', function (): void {
    $mask = NotificationOptInEnum::mask(NotificationOptInEnum::Email, NotificationOptInEnum::Push);
    expect($mask->toInt())->toBe(5);
    expect($mask->names())->toBe(['Email', 'Push']);
});

it('hydrates from int via fromMask', function (): void {
    $mask = BasicPermissionEnum::fromMask(7);
    expect($mask->names())->toBe(['Read', 'Write', 'Delete']);
    expect($mask->toInt())->toBe(7);
});

it('returns null from tryMask(null)', function (): void {
    expect(BasicPermissionEnum::tryMask(null))->toBeNull();
});

it('is immutable on add/remove', function (): void {
    $mask = BasicPermissionEnum::mask(BasicPermissionEnum::Read);
    $newMask = $mask->add(BasicPermissionEnum::Admin);
    expect($mask->toInt())->toBe(1);
    expect($newMask->toInt())->toBe(9);
});

it('counts members', function (): void {
    $mask = BasicPermissionEnum::mask(
        BasicPermissionEnum::Read,
        BasicPermissionEnum::Write,
        BasicPermissionEnum::Delete,
    );
    expect($mask->count())->toBe(3);
});

it('serializes to json', function (): void {
    $mask = BasicPermissionEnum::mask(BasicPermissionEnum::Read, BasicPermissionEnum::Write);
    $json = json_decode((string) json_encode($mask), true);
    expect($json)->toMatchArray(['mask' => 3, 'cases' => ['Read', 'Write']]);
});
