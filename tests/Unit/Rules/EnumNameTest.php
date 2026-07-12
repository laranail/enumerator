<?php

declare(strict_types=1);

use Simtabi\Laranail\Enumerator\Presets\Enums\StatusEnum;
use Simtabi\Laranail\Enumerator\Rules\EnumName;
use Simtabi\Laranail\Enumerator\Tests\Fixtures\Enums\LegacyStatusEnum;

// EnumName — validation rule for case names.

function runEnumName(EnumName $rule, mixed $value): array
{
    $failures = [];
    $rule->validate('attr', $value, function (string $msg) use (&$failures): void {
        $failures[] = $msg;
    });

    return $failures;
}

it('for() is a static factory', function (): void {
    expect(EnumName::for(StatusEnum::class))->toBeInstanceOf(EnumName::class);
});

it('passes for a valid case name', function (): void {
    $rule = new EnumName(StatusEnum::class);
    expect(runEnumName($rule, 'Active'))->toBe([]);
});

it('fails for an unknown case name', function (): void {
    $rule = new EnumName(StatusEnum::class);
    expect(runEnumName($rule, 'Bogus'))->not->toBe([]);
});

it('fails for non-string values', function (): void {
    $rule = new EnumName(StatusEnum::class);
    expect(runEnumName($rule, 42))->not->toBe([]);
});

it('passes for a class-const enum case name', function (): void {
    $rule = new EnumName(LegacyStatusEnum::class);
    expect(runEnumName($rule, 'ACTIVE'))->toBe([]);
});

it('fails for an unknown class-const name', function (): void {
    $rule = new EnumName(LegacyStatusEnum::class);
    expect(runEnumName($rule, 'NONEXISTENT'))->not->toBe([]);
});
