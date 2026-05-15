<?php

declare(strict_types=1);

use Simtabi\Laranail\Enumerator\AbstractEnumeratorClass;
use Simtabi\Laranail\Enumerator\Attributes\Color;
use Simtabi\Laranail\Enumerator\Attributes\Label;
use Simtabi\Laranail\Enumerator\Exceptions\InvalidEnumeratorNameException;
use Simtabi\Laranail\Enumerator\Exceptions\InvalidEnumeratorValueException;

/**
 * @method static LegacyTestEnum ACTIVE()
 * @method static LegacyTestEnum INACTIVE()
 * @method static LegacyTestEnum BANNED()
 */
class LegacyTestEnum extends AbstractEnumeratorClass
{
    #[Label('Active'), Color('success')]
    public const ACTIVE = 'active';

    #[Label('Inactive'), Color('ghost')]
    public const INACTIVE = 'inactive';

    #[Label('Banned'), Color('danger')]
    public const BANNED = 'banned';
}

it('provides static case access via const-named methods', function (): void {
    $case = LegacyTestEnum::ACTIVE();
    expect($case->getValue())->toBe('active');
    expect($case->getKey())->toBe('ACTIVE');
    expect((string) $case)->toBe('active');
});

it('resolves attributes on class-const enums', function (): void {
    expect(LegacyTestEnum::ACTIVE()->label())->toBe('Active');
    expect(LegacyTestEnum::ACTIVE()->color())->toBe('success');
    expect(LegacyTestEnum::BANNED()->color())->toBe('danger');
});

it('compares legacy enums by value', function (): void {
    $a = LegacyTestEnum::ACTIVE();
    $b = LegacyTestEnum::ACTIVE();
    $c = LegacyTestEnum::BANNED();
    expect($a->equals($b))->toBeTrue();
    expect($a->equals($c))->toBeFalse();
    expect($a->is('active'))->toBeTrue();
    expect($a->is('ACTIVE'))->toBeTrue();
    expect($a->is(LegacyTestEnum::ACTIVE()))->toBeTrue();
});

it('throws on unknown constants', function (): void {
    LegacyTestEnum::UNKNOWN();
})->throws(InvalidEnumeratorNameException::class);

it('throws on invalid values', function (): void {
    LegacyTestEnum::fromValue('nope');
})->throws(InvalidEnumeratorValueException::class);

it('exposes cases() and collection helpers', function (): void {
    $cases = LegacyTestEnum::cases();
    expect($cases)->toHaveCount(3);
    expect(LegacyTestEnum::keys())->toBe(['ACTIVE', 'INACTIVE', 'BANNED']);
    expect(LegacyTestEnum::values())->toBe(['active', 'inactive', 'banned']);
    expect(LegacyTestEnum::labels())->toBe(['active' => 'Active', 'inactive' => 'Inactive', 'banned' => 'Banned']);
});

it('validates membership', function (): void {
    expect(LegacyTestEnum::isValid('active'))->toBeTrue();
    expect(LegacyTestEnum::isValid('nope'))->toBeFalse();
    expect(LegacyTestEnum::hasCase('ACTIVE'))->toBeTrue();
});
