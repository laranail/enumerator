<?php

declare(strict_types=1);

use Simtabi\Laranail\Enumerator\Presets\Enums\StatusEnum;
use Simtabi\Laranail\Enumerator\Rules\EnumIn;

// EnumIn — restrict to a subset of cases (delegates to EnumValue::only).

function runEnumIn(EnumIn $rule, mixed $value): array
{
    $failures = [];
    $rule->validate('attr', $value, function (string $msg) use (&$failures): void {
        $failures[] = $msg;
    });

    return $failures;
}

it('passes for a value in the allowed set', function (): void {
    $rule = new EnumIn(StatusEnum::class, [StatusEnum::Active, StatusEnum::Pending]);
    expect(runEnumIn($rule, 'active'))->toBe([]);
    expect(runEnumIn($rule, 'pending'))->toBe([]);
});

it('fails for a value outside the allowed set', function (): void {
    $rule = new EnumIn(StatusEnum::class, [StatusEnum::Active]);
    expect(runEnumIn($rule, 'archived'))->not->toBe([]);
});

it('fails for an unrecognised value entirely', function (): void {
    $rule = new EnumIn(StatusEnum::class, [StatusEnum::Active]);
    expect(runEnumIn($rule, 'nothing'))->not->toBe([]);
});
