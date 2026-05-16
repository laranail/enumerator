<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Tests\Fixtures\Enums;

use Simtabi\Laranail\Enumerator\AbstractEnumeratorClass;
use Simtabi\Laranail\Enumerator\Attributes\Bit;
use Simtabi\Laranail\Enumerator\Attributes\Label;

/**
 * Class-const fixture for AttributesCache::validateBits() duplicate-bit
 * branch on the non-native-enum path.
 *
 * @method static ClassConstDuplicateBitEnum A()
 * @method static ClassConstDuplicateBitEnum B()
 */
class ClassConstDuplicateBitEnum extends AbstractEnumeratorClass
{
    #[Bit(1), Label('A')]
    public const A = 'a';

    #[Bit(1), Label('B')]
    public const B = 'b';
}
