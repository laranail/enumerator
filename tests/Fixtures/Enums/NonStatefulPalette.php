<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Tests\Fixtures\Enums;

/**
 * Pure enum fixture for the PR-β2 defensive guard tests on
 * `Integrations\Livewire\WithEnumTransitions`. Implements neither
 * `Enumerator` nor `Stateful` — used as a "wrong-shape target"
 * passed to `transitionEnum` / `canTransitionEnum` so the
 * `instanceof Stateful` guard rejects it.
 */
enum NonStatefulPalette
{
    case Red;
    case Green;
    case Blue;
}
