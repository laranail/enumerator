<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Model;
use Simtabi\Laranail\Enumerator\Casts\AsBitmask;
use Simtabi\Laranail\Enumerator\Helpers\Bitmask;
use Simtabi\Laranail\Enumerator\Tests\Fixtures\Enums\FlaggedPermissionEnum;

// AsBitmask — Eloquent cast for a Bitwise enum's combined mask.

function asBitmaskModel(): Model
{
    return new class extends Model {};
}

it('of() returns the cast spec string', function (): void {
    expect(AsBitmask::of(FlaggedPermissionEnum::class))
        ->toBe(AsBitmask::class . ':' . FlaggedPermissionEnum::class);
});

it('constructor rejects non-Bitwise classes', function (): void {
    new AsBitmask(stdClass::class);
})->throws(InvalidArgumentException::class);

it('get() returns null for a null value', function (): void {
    $cast = new AsBitmask(FlaggedPermissionEnum::class);
    expect($cast->get(asBitmaskModel(), 'flags', null, []))->toBeNull();
});

it('get() returns the same Bitmask when one is passed', function (): void {
    $mask = new Bitmask(FlaggedPermissionEnum::class, [FlaggedPermissionEnum::Read]);
    $cast = new AsBitmask(FlaggedPermissionEnum::class);
    expect($cast->get(asBitmaskModel(), 'flags', $mask, []))->toBe($mask);
});

it('get() hydrates an int into a Bitmask', function (): void {
    $cast = new AsBitmask(FlaggedPermissionEnum::class);
    $hydrated = $cast->get(asBitmaskModel(), 'flags', 3, []);
    expect($hydrated)->toBeInstanceOf(Bitmask::class);
    expect($hydrated->toInt())->toBe(3);
});

it('get() returns null when the stored value is non-numeric', function (): void {
    $cast = new AsBitmask(FlaggedPermissionEnum::class);
    expect($cast->get(asBitmaskModel(), 'flags', 'not-a-number', []))->toBeNull();
});

it('set() returns null for a null value', function (): void {
    $cast = new AsBitmask(FlaggedPermissionEnum::class);
    expect($cast->set(asBitmaskModel(), 'flags', null, []))->toBeNull();
});

it('set() persists a Bitmask to its int representation', function (): void {
    $mask = new Bitmask(FlaggedPermissionEnum::class, [
        FlaggedPermissionEnum::Read,
        FlaggedPermissionEnum::Write,
    ]);
    $cast = new AsBitmask(FlaggedPermissionEnum::class);
    expect($cast->set(asBitmaskModel(), 'flags', $mask, []))->toBe(3);
});

it('set() wraps a single Bitwise case into a 1-case mask', function (): void {
    $cast = new AsBitmask(FlaggedPermissionEnum::class);
    expect($cast->set(asBitmaskModel(), 'flags', FlaggedPermissionEnum::Read, []))
        ->toBe(1);
    expect($cast->set(asBitmaskModel(), 'flags', FlaggedPermissionEnum::Admin, []))
        ->toBe(8);
});

it('set() passes through a raw int', function (): void {
    $cast = new AsBitmask(FlaggedPermissionEnum::class);
    expect($cast->set(asBitmaskModel(), 'flags', 5, []))->toBe(5);
});

it('set() coerces a numeric string to int', function (): void {
    $cast = new AsBitmask(FlaggedPermissionEnum::class);
    expect($cast->set(asBitmaskModel(), 'flags', '7', []))->toBe(7);
});

it('set() returns null for unsupported values', function (): void {
    $cast = new AsBitmask(FlaggedPermissionEnum::class);
    expect($cast->set(asBitmaskModel(), 'flags', new stdClass, []))->toBeNull();
});
