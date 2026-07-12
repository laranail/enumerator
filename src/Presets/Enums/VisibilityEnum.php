<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Presets\Enums;

use Simtabi\Laranail\Enumerator\Attributes\Color;
use Simtabi\Laranail\Enumerator\Attributes\Icon;
use Simtabi\Laranail\Enumerator\Attributes\Label;
use Simtabi\Laranail\Enumerator\Attributes\Order;
use Simtabi\Laranail\Enumerator\Concerns\HasEnumeratorBehavior;
use Simtabi\Laranail\Enumerator\Concerns\HasOrder;
use Simtabi\Laranail\Enumerator\Contracts\Enumerator;

enum VisibilityEnum: string implements Enumerator
{
    use HasEnumeratorBehavior;
    use HasOrder;

    #[Label('Public'),     Color('success'),   Icon('globe'), Order(10)] case Public = 'public';
    #[Label('Private'),    Color('danger'),    Icon('lock'),  Order(20)] case Private = 'private';
    #[Label('Unlisted'),   Color('secondary'), Icon('eye-off'), Order(30)] case Unlisted = 'unlisted';
    #[Label('Restricted'), Color('warning'),   Icon('shield'), Order(40)] case Restricted = 'restricted';
    #[Label('Internal'),   Color('info'),      Icon('users'), Order(50)] case Internal = 'internal';
}
