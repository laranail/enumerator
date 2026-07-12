<?php

declare(strict_types=1);

use Simtabi\Laranail\Enumerator\Presets\Enums\PublicationStatusEnum;
use Simtabi\Laranail\Enumerator\Presets\Enums\StatusEnum;
use Simtabi\Laranail\Enumerator\Rules\EnumTransition;

// EnumTransition — validation rule for state-machine transitions.

function runEnumTransition(EnumTransition $rule, mixed $value): array
{
    $failures = [];
    $rule->validate('attr', $value, function (string $msg) use (&$failures): void {
        $failures[] = $msg;
    });

    return $failures;
}

it('fails when the enum class is not Stateful', function (): void {
    $rule = new EnumTransition(StatusEnum::class);
    expect(runEnumTransition($rule, 'active'))->not->toBe([]);
});

it('without a $from passes only for initial states', function (): void {
    $rule = new EnumTransition(PublicationStatusEnum::class);
    expect(runEnumTransition($rule, 'draft'))->toBe([]);
});

it('without a $from fails for non-initial states', function (): void {
    $rule = new EnumTransition(PublicationStatusEnum::class);
    expect(runEnumTransition($rule, 'published'))->not->toBe([]);
});

it('with a $from passes for allowed transitions', function (): void {
    $rule = new EnumTransition(PublicationStatusEnum::class, PublicationStatusEnum::Draft);
    expect(runEnumTransition($rule, 'pending'))->toBe([]);
    expect(runEnumTransition($rule, 'archived'))->toBe([]);
});

it('with a $from fails for disallowed transitions', function (): void {
    $rule = new EnumTransition(PublicationStatusEnum::class, PublicationStatusEnum::Draft);
    expect(runEnumTransition($rule, 'published'))->not->toBe([]);
});

it('fails for an unrecognised target value', function (): void {
    $rule = new EnumTransition(PublicationStatusEnum::class, PublicationStatusEnum::Draft);
    expect(runEnumTransition($rule, 'nonexistent'))->not->toBe([]);
});

it('terminal states have no allowed transitions', function (): void {
    $rule = new EnumTransition(PublicationStatusEnum::class, PublicationStatusEnum::Deleted);
    expect(runEnumTransition($rule, 'draft'))->not->toBe([]);
});
