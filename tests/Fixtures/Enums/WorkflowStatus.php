<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Tests\Fixtures\Enums;

use Simtabi\Laranail\Enumerator\Attributes\Label;
use Simtabi\Laranail\Enumerator\Concerns\HasEnumerator;
use Simtabi\Laranail\Enumerator\Contracts\Enumerator;
use Simtabi\Laranail\Enumerator\Contracts\Stateful;

/**
 * Multi-state workflow fixture used by the real-Livewire-roundtrip
 * tests for `WithEnumTransitions` (PR-ω, v0.5.0).
 *
 * Draft → Submitted → Approved → Published (terminal)
 *               └→ Rejected → Draft
 */
enum WorkflowStatus: string implements Enumerator, Stateful
{
    use HasEnumerator;

    #[Label('Draft')] case Draft = 'draft';
    #[Label('Submitted')] case Submitted = 'submitted';
    #[Label('Approved')] case Approved = 'approved';
    #[Label('Rejected')] case Rejected = 'rejected';
    #[Label('Published')] case Published = 'published';

    public static function initialStates(): array
    {
        return [self::Draft];
    }

    public static function transitions(): array
    {
        return [
            self::Draft->value => [self::Submitted],
            self::Submitted->value => [self::Approved, self::Rejected],
            self::Approved->value => [self::Published],
            self::Rejected->value => [self::Draft],
            self::Published->value => [],
        ];
    }
}
