<?php

declare(strict_types=1);

use Simtabi\Laranail\Enumerator\Presets\Enums\BasicPermissionEnum;
use Simtabi\Laranail\Enumerator\Presets\Enums\PriorityEnum;
use Simtabi\Laranail\Enumerator\Presets\Enums\WeekdayEnum;
use Simtabi\Laranail\Enumerator\Tests\Fixtures\Enums\AttributedStatusEnum;
use Simtabi\Laranail\Enumerator\Tests\Fixtures\Enums\SimpleStatusEnum;

it('exposes value/name/label on a case', function (): void {
    $case = SimpleStatusEnum::Active;
    expect($case->value)->toBe('active');
    expect($case->name)->toBe('Active');
    expect($case->label())->toBe('Active'); // humanised fallback
});

it('returns all cases via values()/names()/labels()', function (): void {
    expect(SimpleStatusEnum::values())->toBe(['active', 'inactive', 'pending']);
    expect(SimpleStatusEnum::names())->toBe(['Active', 'Inactive', 'Pending']);
    expect(array_keys(SimpleStatusEnum::labels()))->toBe(['active', 'inactive', 'pending']);
});

it('checks equality + membership', function (): void {
    $case = SimpleStatusEnum::Active;
    expect($case->is('active'))->toBeTrue();
    expect($case->is('Active'))->toBeTrue();
    expect($case->is(SimpleStatusEnum::Inactive))->toBeFalse();
    expect($case->in([SimpleStatusEnum::Active, SimpleStatusEnum::Pending]))->toBeTrue();
    expect($case->notIn([SimpleStatusEnum::Inactive]))->toBeTrue();
});

it('reads PHP attributes via HasAttributes', function (): void {
    $case = AttributedStatusEnum::Active;
    expect($case->label())->toBe('Active');
    expect($case->color())->toBe('success');
    expect($case->icon())->toBe('check');
    expect($case->order())->toBe(10);
    expect($case->meta('notify'))->toBeTrue();
    expect(AttributedStatusEnum::Banned->meta('audit'))->toBeTrue();
    expect(AttributedStatusEnum::classDescription())->toBe('Attributed status fixture');
});

it('hydrates from name', function (): void {
    expect(SimpleStatusEnum::tryFromName('Active'))->toBe(SimpleStatusEnum::Active);
    expect(SimpleStatusEnum::tryFromName('Nope'))->toBeNull();
});

it('compares priorities by Order', function (): void {
    expect(PriorityEnum::Critical->isHigherThan(PriorityEnum::Low))->toBeTrue();
    expect(PriorityEnum::Low->isLowerThan(PriorityEnum::Critical))->toBeTrue();
    expect(PriorityEnum::sortedByOrder()->first())->toBe(PriorityEnum::Low);
    expect(PriorityEnum::sortedByOrderDesc()->first())->toBe(PriorityEnum::Critical);
});

it('builds bitmasks across mixed backing types', function (): void {
    $mask = BasicPermissionEnum::mask(BasicPermissionEnum::Read, BasicPermissionEnum::Write);
    expect($mask->toInt())->toBe(3);
    expect($mask->has(BasicPermissionEnum::Read))->toBeTrue();
    expect($mask->has(BasicPermissionEnum::Admin))->toBeFalse();

    $rehydrated = BasicPermissionEnum::fromMask(7);
    expect($rehydrated->names())->toBe(['Read', 'Write', 'Delete']);
});

it('hydrates weekdays in all three conventions', function (): void {
    expect(WeekdayEnum::Sunday->number())->toBe(1);
    expect(WeekdayEnum::Sunday->isoNumber())->toBe(7);
    expect(WeekdayEnum::Sunday->carbonIndex())->toBe(0);

    expect(WeekdayEnum::fromNumber(1, 'sunday-first'))->toBe(WeekdayEnum::Sunday);
    expect(WeekdayEnum::fromNumber(1, 'iso-8601'))->toBe(WeekdayEnum::Monday);
    expect(WeekdayEnum::fromNumber(0, 'carbon'))->toBe(WeekdayEnum::Sunday);
});
