<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Tests\Fixtures\Enums;

use Simtabi\Laranail\Enumerator\Concerns\HasEnumerator;
use Simtabi\Laranail\Enumerator\Contracts\Enumerator;

/**
 * Fixture exercising HasMagicComparisons' ambiguous-resolution branches.
 *
 * Cases `Active` and `active` differ only by capitalisation, so a
 * case-insensitive call like `$case->isactive()` matches both, forcing
 * the resolver into the multi-hit branch.
 */
enum CasingAmbiguousEnum: string implements Enumerator
{
    use HasEnumerator;

    case ACTIVE = 'A';
    case Active = 'a';
    case Pending = 'P';
}
