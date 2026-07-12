<?php

declare(strict_types=1);

use Livewire\Component;
use Livewire\Livewire;
use Simtabi\Laranail\Enumerator\Integrations\Livewire\WithEnumTransitions;
use Simtabi\Laranail\Enumerator\Tests\Fixtures\Enums\WorkflowStatus;

// PR-ω real-Livewire-roundtrip coverage for the v0.3.0 PR-ζ trait
// (and v0.4.0 PR-ν bulk/validate extensions). The existing
// WithEnumTransitionsTest stubs the error bag + dispatch hook to
// avoid booting a real Livewire request — fast but doesn't pin the
// trait's behaviour through the actual lifecycle.
//
// This file boots a real Livewire component via Livewire::test() and
// exercises the trait against the genuine error-bag + event-bus
// machinery. Graduates WithEnumTransitions from "experimental" to
// "stable enough to lock at v1.0" per the v1.0.0 roadmap.
//
// Skipped automatically when livewire/livewire isn't installed
// (it's require-dev per ADR-0006).

beforeEach(function (): void {
    if (! class_exists(Livewire::class)) {
        $this->markTestSkipped('livewire/livewire not installed (require-dev only).');
    }
});

class WorkflowComponent extends Component
{
    use WithEnumTransitions;

    public WorkflowStatus $status = WorkflowStatus::Draft;

    public WorkflowStatus $secondaryStatus = WorkflowStatus::Draft;

    public function submit(): void
    {
        $this->transitionEnum('status', WorkflowStatus::Submitted);
    }

    public function approve(): void
    {
        $this->transitionEnum('status', WorkflowStatus::Approved);
    }

    public function publish(): void
    {
        $this->transitionEnum('status', WorkflowStatus::Published);
    }

    public function shipAll(): void
    {
        $this->bulkTransitionEnum(['status', 'secondaryStatus'], WorkflowStatus::Submitted);
    }

    public function approveWithCustomMessage(): void
    {
        $this->transitionEnumOrValidate(
            'status',
            WorkflowStatus::Approved,
            messages: ['invalid' => 'You must submit before approving.'],
        );
    }

    public function render(): string
    {
        return '<div></div>';
    }
}

// === transitionEnum() through real Livewire lifecycle =====================

it('transitionEnum advances the Livewire property on a valid transition', function (): void {
    Livewire::test(WorkflowComponent::class)
        ->call('submit')
        ->assertSet('status', WorkflowStatus::Submitted);
});

it('transitionEnum dispatches enumerator.transitioned on success', function (): void {
    Livewire::test(WorkflowComponent::class)
        ->call('submit')
        ->assertDispatched('enumerator.transitioned');
});

it('transitionEnum on an invalid transition leaves state unchanged + sets error', function (): void {
    // Draft → Approved is not allowed (Draft → Submitted → Approved is).
    Livewire::test(WorkflowComponent::class)
        ->call('approve')
        ->assertSet('status', WorkflowStatus::Draft)
        ->assertHasErrors('status')
        ->assertNotDispatched('enumerator.transitioned');
});

it('transitionEnum chained through the state machine advances correctly', function (): void {
    Livewire::test(WorkflowComponent::class)
        ->call('submit')
        ->assertSet('status', WorkflowStatus::Submitted)
        ->call('approve')
        ->assertSet('status', WorkflowStatus::Approved)
        ->call('publish')
        ->assertSet('status', WorkflowStatus::Published);
});

it('transitionEnum on a terminal state errors instead of advancing', function (): void {
    Livewire::test(WorkflowComponent::class, ['status' => WorkflowStatus::Published])
        ->call('approve')
        ->assertSet('status', WorkflowStatus::Published)
        ->assertHasErrors('status');
});

// === bulkTransitionEnum() through real Livewire lifecycle ================

it('bulkTransitionEnum advances every Stateful property on success', function (): void {
    Livewire::test(WorkflowComponent::class)
        ->call('shipAll')
        ->assertSet('status', WorkflowStatus::Submitted)
        ->assertSet('secondaryStatus', WorkflowStatus::Submitted);
});

it('bulkTransitionEnum failure on one path leaves that path unchanged + errors', function (): void {
    // Pre-advance secondaryStatus to Published (terminal) so the
    // Submitted target is invalid for it. status still transitions.
    Livewire::test(WorkflowComponent::class, ['secondaryStatus' => WorkflowStatus::Published])
        ->call('shipAll')
        ->assertSet('status', WorkflowStatus::Submitted)
        ->assertSet('secondaryStatus', WorkflowStatus::Published)
        ->assertHasErrors('secondaryStatus');
});

// === transitionEnumOrValidate() through real Livewire lifecycle ==========

it('transitionEnumOrValidate uses the caller-supplied invalid message', function (): void {
    Livewire::test(WorkflowComponent::class)
        ->call('approveWithCustomMessage')
        ->assertSet('status', WorkflowStatus::Draft)
        ->assertHasErrors(['status' => 'You must submit before approving.']);
});

it('transitionEnumOrValidate succeeds + dispatches on a valid transition', function (): void {
    Livewire::test(WorkflowComponent::class, ['status' => WorkflowStatus::Submitted])
        ->call('approveWithCustomMessage')
        ->assertSet('status', WorkflowStatus::Approved)
        ->assertDispatched('enumerator.transitioned');
});

// === Sanity: trait can co-exist with Livewire's own validation rules =====

it('trait composes with Livewire properties + does not interfere with hydration', function (): void {
    // The transition's data_get / data_set sequence shouldn't trip
    // Livewire's property-hydration guard for typed enum properties.
    $component = Livewire::test(WorkflowComponent::class, [
        'status' => WorkflowStatus::Submitted,
        'secondaryStatus' => WorkflowStatus::Submitted,
    ]);

    $component->assertSet('status', WorkflowStatus::Submitted);
    $component->assertSet('secondaryStatus', WorkflowStatus::Submitted);

    $component->call('approve');
    $component->assertSet('status', WorkflowStatus::Approved);
});
