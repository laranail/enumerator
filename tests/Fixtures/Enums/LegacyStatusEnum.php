<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Tests\Fixtures\Enums;

use Simtabi\Laranail\Enumerator\AbstractEnumeratorClass;
use Simtabi\Laranail\Enumerator\Attributes\Color;
use Simtabi\Laranail\Enumerator\Attributes\Label;

/**
 * Class-const fixture — exercises the AbstractEnumeratorClass path so
 * we can verify integration coverage parity with native enums
 * (the sweep).
 */
class LegacyStatusEnum extends AbstractEnumeratorClass
{
    #[Label('Active'),   Color('success')]
    public const ACTIVE = 'active';

    #[Label('Inactive'), Color('ghost')]
    public const INACTIVE = 'inactive';

    #[Label('Banned'),   Color('danger')]
    public const BANNED = 'banned';
}
