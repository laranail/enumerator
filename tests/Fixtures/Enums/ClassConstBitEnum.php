<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Tests\Fixtures\Enums;

use Simtabi\Laranail\Enumerator\AbstractEnumeratorClass;
use Simtabi\Laranail\Enumerator\Attributes\Bit;
use Simtabi\Laranail\Enumerator\Attributes\Label;

/**
 * Class-const fixture for AttributesCache::validateBits() — exercises
 * the `else` branch that iterates ReflectionClassConstant + Bit::class
 * attributes for non-native-enum classes.
 *
 * @method static ClassConstBitEnum READ()
 * @method static ClassConstBitEnum WRITE()
 */
class ClassConstBitEnum extends AbstractEnumeratorClass
{
    #[Bit(1), Label('Read')]
    public const READ = 'read';

    #[Bit(2), Label('Write')]
    public const WRITE = 'write';
}
