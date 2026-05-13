<?php

declare(strict_types=1);

use Simtabi\Laranail\Enumerator\Contracts\TransitionHook;
use Simtabi\Laranail\Enumerator\Exceptions\InvalidTransitionException;
use Simtabi\Laranail\Enumerator\Presets\Enums\PublicationStatusEnum;

// HasTransitions — state-machine transition logic.

it('allowedTransitions() returns the transition targets for the case', function (): void {
    $allowed = PublicationStatusEnum::Draft->allowedTransitions();
    expect($allowed)->toContain(PublicationStatusEnum::Pending);
    expect($allowed)->toContain(PublicationStatusEnum::Archived);
});

it('allowedTransitions() returns empty for a terminal state', function (): void {
    expect(PublicationStatusEnum::Deleted->allowedTransitions())->toBe([]);
});

it('canTransitionTo() reflects the allowed map', function (): void {
    expect(PublicationStatusEnum::Draft->canTransitionTo(PublicationStatusEnum::Pending))->toBeTrue();
    expect(PublicationStatusEnum::Draft->canTransitionTo(PublicationStatusEnum::Published))->toBeFalse();
});

it('transitionTo() returns the target on success', function (): void {
    expect(PublicationStatusEnum::Draft->transitionTo(PublicationStatusEnum::Pending))
        ->toBe(PublicationStatusEnum::Pending);
});

it('transitionTo() throws when the transition is disallowed', function (): void {
    PublicationStatusEnum::Draft->transitionTo(PublicationStatusEnum::Published);
})->throws(InvalidTransitionException::class);

it('tryTransitionTo() returns null when disallowed', function (): void {
    expect(PublicationStatusEnum::Draft->tryTransitionTo(PublicationStatusEnum::Published))->toBeNull();
});

it('tryTransitionTo() returns the target when allowed', function (): void {
    expect(PublicationStatusEnum::Draft->tryTransitionTo(PublicationStatusEnum::Pending))
        ->toBe(PublicationStatusEnum::Pending);
});

it('isTerminal() identifies terminal states', function (): void {
    expect(PublicationStatusEnum::Deleted->isTerminal())->toBeTrue();
    expect(PublicationStatusEnum::Draft->isTerminal())->toBeFalse();
});

it('isInitialState() reflects the declared initialStates() set', function (): void {
    expect(PublicationStatusEnum::Draft->isInitialState())->toBeTrue();
    expect(PublicationStatusEnum::Pending->isInitialState())->toBeFalse();
});

it('transitionTo() invokes hook.before / hook.after on success', function (): void {
    $events = [];
    $hook = new class($events) implements TransitionHook
    {
        public function __construct(public array &$events) {}

        public function before(object $from, object $to): bool
        {
            $this->events[] = 'before:' . $from->name . '->' . $to->name;

            return true;
        }

        public function after(object $from, object $to): void
        {
            $this->events[] = 'after:' . $from->name . '->' . $to->name;
        }
    };

    PublicationStatusEnum::Draft->transitionTo(PublicationStatusEnum::Pending, $hook);

    expect($events)->toBe([
        'before:Draft->Pending',
        'after:Draft->Pending',
    ]);
});

it('transitionTo() throws when hook.before returns false', function (): void {
    $hook = new class implements TransitionHook
    {
        public function before(object $from, object $to): bool
        {
            return false;
        }

        public function after(object $from, object $to): void {}
    };

    PublicationStatusEnum::Draft->transitionTo(PublicationStatusEnum::Pending, $hook);
})->throws(InvalidTransitionException::class);
