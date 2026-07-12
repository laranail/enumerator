<?php

declare(strict_types=1);

use Simtabi\Laranail\Enumerator\Exceptions\InvalidBitmaskException;
use Simtabi\Laranail\Enumerator\Helpers\Bitmask;
use Simtabi\Laranail\Enumerator\Presets\Enums\StatusEnum;
use Simtabi\Laranail\Enumerator\Tests\Fixtures\Enums\FlaggedPermissionEnum;

// Bitmask — value object combining Bitwise enum cases into an integer mask.

it('constructor accepts an empty case list', function (): void {
    $mask = new Bitmask(FlaggedPermissionEnum::class, []);
    expect($mask->toInt())->toBe(0);
    expect($mask->count())->toBe(0);
});

it('constructor rejects non-Bitwise enum classes', function (): void {
    new Bitmask(stdClass::class, []);
})->throws(InvalidBitmaskException::class);

it('constructor rejects cases that do not belong to the enum class', function (): void {
    new Bitmask(FlaggedPermissionEnum::class, [StatusEnum::Active]);
})->throws(InvalidBitmaskException::class);

it('constructor de-duplicates repeated cases', function (): void {
    $mask = new Bitmask(FlaggedPermissionEnum::class, [
        FlaggedPermissionEnum::Read,
        FlaggedPermissionEnum::Read,
        FlaggedPermissionEnum::Write,
    ]);
    expect($mask->count())->toBe(2);
});

it('toInt() OR-combines the #[Bit] attribute values', function (): void {
    $mask = new Bitmask(FlaggedPermissionEnum::class, [
        FlaggedPermissionEnum::Read,
        FlaggedPermissionEnum::Write,
    ]);
    expect($mask->toInt())->toBe(3);

    $allFour = new Bitmask(FlaggedPermissionEnum::class, [
        FlaggedPermissionEnum::Read,
        FlaggedPermissionEnum::Write,
        FlaggedPermissionEnum::Delete,
        FlaggedPermissionEnum::Admin,
    ]);
    expect($allFour->toInt())->toBe(15);
});

it('has() reflects membership', function (): void {
    $mask = new Bitmask(FlaggedPermissionEnum::class, [FlaggedPermissionEnum::Read]);
    expect($mask->has(FlaggedPermissionEnum::Read))->toBeTrue();
    expect($mask->has(FlaggedPermissionEnum::Admin))->toBeFalse();
});

it('hasAll() / hasAny() / hasNone() implement the obvious quantifiers', function (): void {
    $mask = new Bitmask(FlaggedPermissionEnum::class, [
        FlaggedPermissionEnum::Read,
        FlaggedPermissionEnum::Write,
    ]);

    expect($mask->hasAll(FlaggedPermissionEnum::Read, FlaggedPermissionEnum::Write))->toBeTrue();
    expect($mask->hasAll(FlaggedPermissionEnum::Read, FlaggedPermissionEnum::Admin))->toBeFalse();

    expect($mask->hasAny(FlaggedPermissionEnum::Admin, FlaggedPermissionEnum::Read))->toBeTrue();
    expect($mask->hasAny(FlaggedPermissionEnum::Admin, FlaggedPermissionEnum::Delete))->toBeFalse();

    expect($mask->hasNone(FlaggedPermissionEnum::Admin, FlaggedPermissionEnum::Delete))->toBeTrue();
    expect($mask->hasNone(FlaggedPermissionEnum::Read))->toBeFalse();
});

it('add() returns a new mask with cases added (immutable)', function (): void {
    $original = new Bitmask(FlaggedPermissionEnum::class, [FlaggedPermissionEnum::Read]);
    $extended = $original->add(FlaggedPermissionEnum::Write);

    expect($original->count())->toBe(1);
    expect($extended->count())->toBe(2);
    expect($extended->toInt())->toBe(3);
});

it('remove() returns a new mask with cases removed (immutable)', function (): void {
    $original = new Bitmask(FlaggedPermissionEnum::class, [
        FlaggedPermissionEnum::Read,
        FlaggedPermissionEnum::Write,
    ]);
    $reduced = $original->remove(FlaggedPermissionEnum::Write);

    expect($original->count())->toBe(2);
    expect($reduced->count())->toBe(1);
    expect($reduced->has(FlaggedPermissionEnum::Read))->toBeTrue();
});

it('all() returns the case array', function (): void {
    $mask = new Bitmask(FlaggedPermissionEnum::class, [
        FlaggedPermissionEnum::Read,
        FlaggedPermissionEnum::Admin,
    ]);
    expect($mask->all())->toBe([FlaggedPermissionEnum::Read, FlaggedPermissionEnum::Admin]);
});

it('values() returns the backing values', function (): void {
    $mask = new Bitmask(FlaggedPermissionEnum::class, [
        FlaggedPermissionEnum::Read,
        FlaggedPermissionEnum::Admin,
    ]);
    expect($mask->values())->toBe([1, 8]);
});

it('names() returns the case names', function (): void {
    $mask = new Bitmask(FlaggedPermissionEnum::class, [
        FlaggedPermissionEnum::Read,
        FlaggedPermissionEnum::Admin,
    ]);
    expect($mask->names())->toBe(['Read', 'Admin']);
});

it('labels() returns the #[Label] strings', function (): void {
    $mask = new Bitmask(FlaggedPermissionEnum::class, [
        FlaggedPermissionEnum::Read,
        FlaggedPermissionEnum::Admin,
    ]);
    expect($mask->labels())->toBe(['Read', 'Admin']);
});

it('getIterator() yields each case', function (): void {
    $mask = new Bitmask(FlaggedPermissionEnum::class, [
        FlaggedPermissionEnum::Read,
        FlaggedPermissionEnum::Write,
    ]);

    $seen = [];
    foreach ($mask as $case) {
        $seen[] = $case;
    }
    expect($seen)->toBe([FlaggedPermissionEnum::Read, FlaggedPermissionEnum::Write]);
});

it('jsonSerialize() emits enum/mask/cases shape', function (): void {
    $mask = new Bitmask(FlaggedPermissionEnum::class, [FlaggedPermissionEnum::Read]);
    $payload = $mask->jsonSerialize();

    expect($payload)->toHaveKey('enum')
        ->toHaveKey('mask')
        ->toHaveKey('cases');
    expect($payload['enum'])->toBe(FlaggedPermissionEnum::class);
    expect($payload['mask'])->toBe(1);
    expect($payload['cases'])->toBe(['Read']);
});

it('__toString() returns the int mask as a string', function (): void {
    $mask = new Bitmask(FlaggedPermissionEnum::class, [
        FlaggedPermissionEnum::Read,
        FlaggedPermissionEnum::Admin,
    ]);
    expect((string) $mask)->toBe('9');
});
