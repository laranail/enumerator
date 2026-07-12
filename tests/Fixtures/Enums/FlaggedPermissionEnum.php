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
 * Bitmask fixture — exercises HasBitmask, the #[Bit] attribute, and
 * the Bitmask value object's round-trip through AsBitmask cast.
 */
enum FlaggedPermissionEnum: int implements Bitwise, Enumerator
{
    use HasBitmask;
    use HasEnumeratorBehavior;

    #[Bit(1), Label('Read')] case Read = 1;
    #[Bit(2), Label('Write')] case Write = 2;
    #[Bit(4), Label('Delete')] case Delete = 4;
    #[Bit(8), Label('Admin')] case Admin = 8;
}
