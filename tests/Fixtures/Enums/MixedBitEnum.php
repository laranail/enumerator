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
 * Fixture for AttributesCache::validateBits() — exercises the
 * "case has no #[Bit]" continue branch by mixing cases that have
 * the attribute with one that doesn't.
 */
enum MixedBitEnum: int implements Bitwise, Enumerator
{
    use HasBitmask;
    use HasEnumeratorBehavior;

    #[Bit(1), Label('Read')] case Read = 1;
    #[Bit(2), Label('Write')] case Write = 2;
    #[Label('Plain')] case Plain = 99;
}
