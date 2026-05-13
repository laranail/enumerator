<?php

declare(strict_types=1);

use Simtabi\Laranail\Enumerator\Support\CasesCollection;
use Simtabi\Laranail\Enumerator\Tests\Fixtures\Enums\BackedIntStatusEnum;
use Simtabi\Laranail\Enumerator\Tests\Fixtures\Enums\OrderedStatusEnum;
use Simtabi\Laranail\Enumerator\Tests\Fixtures\Enums\RenderableStatusEnum;

// Tests the new `HasEnumerator` umbrella trait by exercising
// at least one method from every composed sub-trait against a fixture
// that `use`s the umbrella.

it('exposes the static collection surface from HasEnumeratorBehavior', function (): void {
    expect(BackedIntStatusEnum::cases())->toHaveCount(3);
    expect(BackedIntStatusEnum::count())->toBe(3);
    expect(BackedIntStatusEnum::collect())->toBeInstanceOf(CasesCollection::class);
    expect(BackedIntStatusEnum::isBacked())->toBeTrue();
});

it('exposes magic comparisons from HasMagicComparisons', function (): void {
    expect(BackedIntStatusEnum::Active->isActive())->toBeTrue();   // dynamic
    expect(BackedIntStatusEnum::Active->isInactive())->toBeFalse();
    expect(BackedIntStatusEnum::Active->isNotInactive())->toBeTrue();
});

it('exposes invokable cases from HasInvokableCases', function (): void {
    // Status::Active() returns the backing value by default.
    $invoked = BackedIntStatusEnum::Active();
    expect($invoked)->toBe(1);

    // Status::Active('label') returns the label.
    expect(BackedIntStatusEnum::Active('label'))->toBe('Active');
});

it('exposes ordering helpers from HasOrder', function (): void {
    $sorted = OrderedStatusEnum::sortedByOrder();
    expect($sorted->first()?->name)->toBe('First');
    expect($sorted->last()?->name)->toBe('Fourth');
    expect(OrderedStatusEnum::Second->isHigherThan(OrderedStatusEnum::First))->toBeTrue();
});

it('exposes lifecycle helpers from HasLifecycle', function (): void {
    expect(OrderedStatusEnum::First->next())->toBe(OrderedStatusEnum::Second);
    expect(OrderedStatusEnum::Fourth->previous())->toBe(OrderedStatusEnum::Third);
    expect(OrderedStatusEnum::First->isFirst())->toBeTrue();
    expect(OrderedStatusEnum::Fourth->isLast())->toBeTrue();
});

it('exposes attribute resolution from HasEnumeratorBehavior umbrella', function (): void {
    expect(RenderableStatusEnum::Active->color())->toBe('success');
    expect(RenderableStatusEnum::Active->icon())->toBe('check-circle');
    expect(RenderableStatusEnum::Active->cssClass('bootstrap'))->toBe('badge bg-success');
    expect(RenderableStatusEnum::Banned->cssClass('bootstrap'))->toBe('badge bg-danger');
});

it('renders HTML via RendersHtml composed into the umbrella', function (): void {
    $html = (string) RenderableStatusEnum::Active->toHtml('bootstrap');
    expect($html)->toContain('badge')
        ->toContain('Active')
        ->toContain('role="status"');
});

it('exposes equality from HasEquality (via HasEnumeratorBehavior)', function (): void {
    expect(BackedIntStatusEnum::Active->is(1))->toBeTrue();
    expect(BackedIntStatusEnum::Active->is('Active'))->toBeTrue();
    expect(BackedIntStatusEnum::Active->is(BackedIntStatusEnum::Active))->toBeTrue();
    expect(BackedIntStatusEnum::Active->in([BackedIntStatusEnum::Active, BackedIntStatusEnum::Pending]))->toBeTrue();
});
