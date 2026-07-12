<?php

declare(strict_types=1);

use Simtabi\Laranail\Enumerator\Presets\Enums\StatusEnum;
use Simtabi\Laranail\Enumerator\Tests\Fixtures\Enums\PureColorEnum;

// IsJsonable — toArray/jsonSerialize/toJson contract.

it('toArray() exposes the canonical case shape (backed)', function (): void {
    $array = StatusEnum::Active->toArray();

    expect($array)->toBeArray()
        ->toHaveKey('name')
        ->toHaveKey('value')
        ->toHaveKey('label');
    expect($array['name'])->toBe('Active');
    expect($array['value'])->toBe('active');
});

it('toArray() for a pure enum omits value or sets it equal to name', function (): void {
    $array = PureColorEnum::Red->toArray();
    expect($array)->toHaveKey('name');
    expect($array['name'])->toBe('Red');
});

it('jsonSerialize() and toArray() agree', function (): void {
    expect(StatusEnum::Active->jsonSerialize())->toBe(StatusEnum::Active->toArray());
});

it('toJson() produces a parseable JSON string', function (): void {
    $json = StatusEnum::Active->toJson();
    expect($json)->toBeString();

    $decoded = json_decode($json, true);
    expect($decoded)->toBeArray()->toHaveKey('name');
});

it('toJson() respects JSON_PRETTY_PRINT flag', function (): void {
    $json = StatusEnum::Active->toJson(JSON_PRETTY_PRINT);
    expect($json)->toContain("\n");
});
