<?php

declare(strict_types=1);

use Simtabi\Laranail\Enumerator\Presets\Enums\StatusEnum;
use Simtabi\Laranail\Enumerator\Rules\EnumNotIn;

// EnumNotIn — exclude a subset of cases (delegates to EnumValue::except).

function runEnumNotIn(EnumNotIn $rule, mixed $value): array
{
    $failures = [];
    $rule->validate('attr', $value, function (string $msg) use (&$failures): void {
        $failures[] = $msg;
    });

    return $failures;
}

it('passes for values not in the excluded set', function (): void {
    $rule = new EnumNotIn(StatusEnum::class, [StatusEnum::Archived]);
    expect(runEnumNotIn($rule, 'active'))->toBe([]);
    expect(runEnumNotIn($rule, 'pending'))->toBe([]);
});

it('fails for values in the excluded set', function (): void {
    $rule = new EnumNotIn(StatusEnum::class, [StatusEnum::Archived]);
    expect(runEnumNotIn($rule, 'archived'))->not->toBe([]);
});

it('fails for unrecognised values', function (): void {
    $rule = new EnumNotIn(StatusEnum::class, [StatusEnum::Archived]);
    expect(runEnumNotIn($rule, 'nothing'))->not->toBe([]);
});
