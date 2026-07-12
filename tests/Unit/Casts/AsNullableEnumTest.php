<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Model;
use Simtabi\Laranail\Enumerator\Casts\AsNullableEnum;
use Simtabi\Laranail\Enumerator\Presets\Enums\StatusEnum;

// AsNullableEnum — forgiving variant of AsEnum (invalid → null).

function asNullableModel(): Model
{
    return new class extends Model {};
}

it('of() returns the cast spec string', function (): void {
    expect(AsNullableEnum::of(StatusEnum::class))
        ->toBe(AsNullableEnum::class . ':' . StatusEnum::class);
});

it('constructor validates the target class', function (): void {
    new AsNullableEnum(stdClass::class);
})->throws(InvalidArgumentException::class);

it('get() returns null for a null value', function (): void {
    $cast = new AsNullableEnum(StatusEnum::class);
    expect($cast->get(asNullableModel(), 'status', null, []))->toBeNull();
});

it('get() hydrates valid values like AsEnum', function (): void {
    $cast = new AsNullableEnum(StatusEnum::class);
    expect($cast->get(asNullableModel(), 'status', 'active', []))
        ->toBe(StatusEnum::Active);
});

it('get() returns null instead of throwing on invalid values', function (): void {
    $cast = new AsNullableEnum(StatusEnum::class);
    expect($cast->get(asNullableModel(), 'status', 'nothing', []))->toBeNull();
});

it('set() returns null for a null value', function (): void {
    $cast = new AsNullableEnum(StatusEnum::class);
    expect($cast->set(asNullableModel(), 'status', null, []))->toBeNull();
});

it('set() persists a valid case to its backing value', function (): void {
    $cast = new AsNullableEnum(StatusEnum::class);
    expect($cast->set(asNullableModel(), 'status', StatusEnum::Active, []))
        ->toBe('active');
});

it('set() returns null instead of throwing on an invalid raw value', function (): void {
    $cast = new AsNullableEnum(StatusEnum::class);
    expect($cast->set(asNullableModel(), 'status', 'nothing', []))->toBeNull();
});
