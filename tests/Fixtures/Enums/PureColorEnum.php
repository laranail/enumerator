<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Tests\Fixtures\Enums;

use Simtabi\Laranail\Enumerator\Attributes\Color;
use Simtabi\Laranail\Enumerator\Attributes\Label;
use Simtabi\Laranail\Enumerator\Concerns\HasEnumeratorBehavior;
use Simtabi\Laranail\Enumerator\Contracts\Enumerator;

/**
 * Pure (no backing) fixture — exercises name-only lookup paths and
 * confirms that `tryFromName()` covers what `tryFrom()` cannot.
 *
 * NOTE: pure enums cannot use `HasInvokableCases` cleanly because the
 * `Color()` method name would collide with the `Color` case. We use
 * `HasEnumeratorBehavior` directly here.
 */
enum PureColorEnum implements Enumerator
{
    use HasEnumeratorBehavior;

    #[Label('Red'),   Color('danger')] case Red;
    #[Label('Green'), Color('success')] case Green;
    #[Label('Blue'),  Color('info')] case Blue;
}
