<?php

declare(strict_types=1);

use Simtabi\Laranail\Enumerator\Exceptions\InvalidTransitionException;
use Simtabi\Laranail\Enumerator\Presets\Enums\OrderStatusEnum;
use Simtabi\Laranail\Enumerator\Presets\Enums\PublicationStatusEnum;

it('allows declared transitions', function (): void {
    expect(OrderStatusEnum::Pending->canTransitionTo(OrderStatusEnum::Confirmed))->toBeTrue();
    expect(OrderStatusEnum::Confirmed->canTransitionTo(OrderStatusEnum::Shipped))->toBeFalse();
    expect(OrderStatusEnum::Confirmed->canTransitionTo(OrderStatusEnum::Processing))->toBeTrue();
});

it('throws on illegal transition', function (): void {
    OrderStatusEnum::Pending->transitionTo(OrderStatusEnum::Delivered);
})->throws(InvalidTransitionException::class);

it('returns null from tryTransitionTo on illegal target', function (): void {
    expect(OrderStatusEnum::Pending->tryTransitionTo(OrderStatusEnum::Delivered))->toBeNull();
});

it('reports terminal states', function (): void {
    // Cancelled and Refunded have no outgoing transitions (terminal).
    // Delivered → Refunded is allowed by the preset, so it is not terminal.
    expect(OrderStatusEnum::Cancelled->isTerminal())->toBeTrue();
    expect(OrderStatusEnum::Refunded->isTerminal())->toBeTrue();
    expect(OrderStatusEnum::Delivered->isTerminal())->toBeFalse();
    expect(OrderStatusEnum::Pending->isTerminal())->toBeFalse();
});

it('reports initial states', function (): void {
    expect(OrderStatusEnum::Pending->isInitialState())->toBeTrue();
    expect(OrderStatusEnum::Shipped->isInitialState())->toBeFalse();
});

it('chains transitions through PublicationStatusEnum lifecycle', function (): void {
    $s = PublicationStatusEnum::Draft;
    $s = $s->transitionTo(PublicationStatusEnum::Pending);
    $s = $s->transitionTo(PublicationStatusEnum::Published);
    $s = $s->transitionTo(PublicationStatusEnum::Archived);
    $s = $s->transitionTo(PublicationStatusEnum::Deleted);
    expect($s)->toBe(PublicationStatusEnum::Deleted);
    expect($s->isTerminal())->toBeTrue();
});
