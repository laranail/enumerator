<?php

declare(strict_types=1);

use Simtabi\Laranail\Enumerator\Concerns\HasEnumeratorBehavior;
use Simtabi\Laranail\Enumerator\Concerns\HasMagicComparisons;
use Simtabi\Laranail\Enumerator\Contracts\Enumerator;

enum MagicTestStatusEnum: string implements Enumerator
{
    use HasEnumeratorBehavior;
    use HasMagicComparisons;

    case Active = 'active';
    case Inactive = 'inactive';
    case Banned = 'banned';
}

it('matches case-insensitively on case names', function (): void {
    expect(MagicTestStatusEnum::Active->isActive())->toBeTrue();
    expect(MagicTestStatusEnum::Active->isactive())->toBeTrue();
    expect(MagicTestStatusEnum::Active->isACTIVE())->toBeTrue();
    expect(MagicTestStatusEnum::Active->isInactive())->toBeFalse();
});

it('supports isNot* negation', function (): void {
    expect(MagicTestStatusEnum::Active->isNotBanned())->toBeTrue();
    expect(MagicTestStatusEnum::Banned->isNotBanned())->toBeFalse();
});

it('supports isOneOf / isNoneOf', function (): void {
    $case = MagicTestStatusEnum::Active;
    expect($case->isOneOf(MagicTestStatusEnum::Active, MagicTestStatusEnum::Inactive))->toBeTrue();
    expect($case->isOneOf(MagicTestStatusEnum::Banned))->toBeFalse();
    expect($case->isNoneOf(MagicTestStatusEnum::Banned))->toBeTrue();
});
