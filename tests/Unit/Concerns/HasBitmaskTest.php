<?php

declare(strict_types=1);

use Simtabi\Laranail\Enumerator\Helpers\Bitmask;
use Simtabi\Laranail\Enumerator\Tests\Fixtures\Enums\FlaggedPermissionEnum;

// HasBitmask — bitmask support for #[Bit]-tagged enums.

it('mask() builds a Bitmask from one or more cases', function (): void {
    $mask = FlaggedPermissionEnum::mask(
        FlaggedPermissionEnum::Read,
        FlaggedPermissionEnum::Write,
    );

    expect($mask)->toBeInstanceOf(Bitmask::class);
    expect($mask->toInt())->toBe(3);
});

it('fromMask() hydrates a Bitmask from an int', function (): void {
    $mask = FlaggedPermissionEnum::fromMask(3);
    expect($mask->has(FlaggedPermissionEnum::Read))->toBeTrue();
    expect($mask->has(FlaggedPermissionEnum::Write))->toBeTrue();
    expect($mask->has(FlaggedPermissionEnum::Delete))->toBeFalse();
});

it('fromMask() ignores undeclared bits', function (): void {
    // 16 isn't declared on any case (1, 2, 4, 8 are).
    $mask = FlaggedPermissionEnum::fromMask(16);
    expect($mask->count())->toBe(0);
    expect($mask->toInt())->toBe(0);
});

it('fromMask() handles the all-flags case', function (): void {
    $mask = FlaggedPermissionEnum::fromMask(15);
    expect($mask->count())->toBe(4);
});

it('tryMask() returns null for null', function (): void {
    expect(FlaggedPermissionEnum::tryMask(null))->toBeNull();
});

it('tryMask() returns a Bitmask for a valid int', function (): void {
    $mask = FlaggedPermissionEnum::tryMask(1);
    expect($mask)->toBeInstanceOf(Bitmask::class);
    expect($mask->toInt())->toBe(1);
});

it('bit() returns the #[Bit] attribute value on a case', function (): void {
    expect(FlaggedPermissionEnum::Read->bit())->toBe(1);
    expect(FlaggedPermissionEnum::Admin->bit())->toBe(8);
});

it('bits() returns a [bit => case-name] map', function (): void {
    $map = FlaggedPermissionEnum::bits();
    expect($map[1])->toBe('Read');
    expect($map[2])->toBe('Write');
    expect($map[4])->toBe('Delete');
    expect($map[8])->toBe('Admin');
});
