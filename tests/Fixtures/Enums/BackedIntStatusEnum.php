<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Tests\Fixtures\Enums;

use Simtabi\Laranail\Enumerator\Attributes\Label;
use Simtabi\Laranail\Enumerator\Concerns\HasEnumerator;
use Simtabi\Laranail\Enumerator\Contracts\Enumerator;

/**
 * Int-backed fixture — exercises value-coercion paths where the value
 * is an integer rather than a string.
 */
enum BackedIntStatusEnum: int implements Enumerator
{
    use HasEnumerator;

    #[Label('Active')] case Active = 1;
    #[Label('Inactive')] case Inactive = 2;
    #[Label('Pending')] case Pending = 3;
}
