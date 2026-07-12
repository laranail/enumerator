<?php

declare(strict_types=1);

use Simtabi\Laranail\Enumerator\Presets\Enums\StatusEnum;
use Simtabi\Laranail\Enumerator\Rules\EnumValue;
use Simtabi\Laranail\Enumerator\Tests\Fixtures\Enums\BackedIntStatusEnum;
use Simtabi\Laranail\Enumerator\Tests\Fixtures\Enums\PureColorEnum;

// EnumValue — validation rule for native enum backing values + name fallback.

function runEnumValue(EnumValue $rule, mixed $value): array
{
    $failures = [];
    $rule->validate('attr', $value, function (string $msg) use (&$failures): void {
        $failures[] = $msg;
    });

    return $failures;
}

it('for() is a static factory', function (): void {
    expect(EnumValue::for(StatusEnum::class))
        ->toBeInstanceOf(EnumValue::class);
});

it('passes for a valid backing value', function (): void {
    $rule = new EnumValue(StatusEnum::class);
    expect(runEnumValue($rule, 'active'))->toBe([]);
});

it('passes for a valid int backing value', function (): void {
    $rule = new EnumValue(BackedIntStatusEnum::class);
    expect(runEnumValue($rule, 1))->toBe([]);
});

it('passes for a pure enum case name (fallback path)', function (): void {
    $rule = new EnumValue(PureColorEnum::class);
    expect(runEnumValue($rule, 'Red'))->toBe([]);
});

it('fails for an unrecognised value', function (): void {
    $rule = new EnumValue(StatusEnum::class);
    expect(runEnumValue($rule, 'nothing'))->not->toBe([]);
});

it('only() restricts the set of allowed cases', function (): void {
    $rule = EnumValue::for(StatusEnum::class)->only([StatusEnum::Active]);
    expect(runEnumValue($rule, 'active'))->toBe([]);
    expect(runEnumValue($rule, 'archived'))->not->toBe([]);
});

it('except() excludes specific cases', function (): void {
    $rule = EnumValue::for(StatusEnum::class)->except([StatusEnum::Archived]);
    expect(runEnumValue($rule, 'active'))->toBe([]);
    expect(runEnumValue($rule, 'archived'))->not->toBe([]);
});
