<?php

declare(strict_types=1);

use Simtabi\Laranail\Enumerator\Modules\Saloon\EnumCaster;
use Simtabi\Laranail\Enumerator\Modules\Saloon\SaloonServiceProvider;
use Simtabi\Laranail\Enumerator\Presets\Enums\StatusEnum;
use Simtabi\Laranail\Enumerator\Tests\Fixtures\Enums\BackedIntStatusEnum;
use Simtabi\Laranail\Enumerator\Tests\Fixtures\Enums\LegacyStatusEnum;
use Simtabi\Laranail\Enumerator\Tests\Fixtures\Enums\PureColorEnum;

it('serializes backed string enums to their value', function (): void {
    expect(EnumCaster::serialize(StatusEnum::Active))->toBe('active');
});

it('serializes backed int enums to their value', function (): void {
    expect(EnumCaster::serialize(BackedIntStatusEnum::Active))->toBe(1);
});

it('serializes pure enums to their name', function (): void {
    expect(EnumCaster::serialize(PureColorEnum::Red))->toBe('Red');
});

it('serializes class-const enums via getValue()', function (): void {
    expect(EnumCaster::serialize(LegacyStatusEnum::ACTIVE()))->toBe('active');
});

it('passes through non-enum scalars unchanged', function (): void {
    expect(EnumCaster::serialize('plain'))->toBe('plain');
    expect(EnumCaster::serialize(42))->toBe(42);
    expect(EnumCaster::serialize(null))->toBeNull();
});

it('recursively casts arrays', function (): void {
    $payload = [
        'status' => StatusEnum::Active,
        'extra' => ['nested' => PureColorEnum::Red, 'static' => 'x'],
    ];

    expect(EnumCaster::cast($payload))->toBe([
        'status' => 'active',
        'extra' => ['nested' => 'Red', 'static' => 'x'],
    ]);
});

it('binds EnumCaster as a singleton when module is enabled', function (): void {
    config()->set('enumerator.modules.saloon', true);
    app()->register(SaloonServiceProvider::class, true);

    expect(app(EnumCaster::class))->toBeInstanceOf(EnumCaster::class);
});
