<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Tests\Fixtures\Enums;

use Simtabi\Laranail\Enumerator\Attributes\Description;
use Simtabi\Laranail\Enumerator\Attributes\Label;
use Simtabi\Laranail\Enumerator\Concerns\HasEnumerator;
use Simtabi\Laranail\Enumerator\Contracts\Enumerator;

/**
 * Fixture with both class-level and case-level #[Description].
 *
 * Exists so the GraphQL / OpenAPI exporters can be tested against an
 * enum that exposes descriptions on every case — exercises the
 * inline-description block emission paths in those exporters.
 */
#[Description('Lifecycle states for fixture round-trip tests.')]
enum DescribedEnum: string implements Enumerator
{
    use HasEnumerator;

    #[Label('Open'), Description('Currently in progress.')]
    case Open = 'open';

    #[Label('Closed'), Description('No longer accepting input.')]
    case Closed = 'closed';
}
