<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Tests\Fixtures\Enums;

use Simtabi\Laranail\Enumerator\Attributes\Bit;
use Simtabi\Laranail\Enumerator\Attributes\Label;
use Simtabi\Laranail\Enumerator\Concerns\HasBitmask;
use Simtabi\Laranail\Enumerator\Concerns\HasEnumeratorBehavior;
use Simtabi\Laranail\Enumerator\Contracts\Bitwise;
use Simtabi\Laranail\Enumerator\Contracts\Enumerator;

/**
 * Fixture for AttributesCache::validateBits() — two cases share the
 * same #[Bit(1)]. Calling validateBits() must throw
 * InvalidBitmaskException.
 */
enum DuplicateBitEnum: int implements Bitwise, Enumerator
{
    use HasBitmask;
    use HasEnumeratorBehavior;

    #[Bit(1), Label('First')] case First = 1;
    #[Bit(1), Label('Second')] case Second = 2;
}
