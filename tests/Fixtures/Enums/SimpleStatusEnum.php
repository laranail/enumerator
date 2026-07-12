<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Tests\Fixtures\Enums;

use Simtabi\Laranail\Enumerator\Concerns\HasEnumeratorBehavior;
use Simtabi\Laranail\Enumerator\Contracts\Enumerator;

enum SimpleStatusEnum: string implements Enumerator
{
    use HasEnumeratorBehavior;

    case Active = 'active';
    case Inactive = 'inactive';
    case Pending = 'pending';
}
