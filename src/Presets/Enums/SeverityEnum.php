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

enum SeverityEnum: int implements Enumerator
{
    use HasEnumeratorBehavior;
    use HasOrder;

    #[Label('Emergency'), Color('danger'),    Icon('siren'),         Order(0)] case Emergency = 0;
    #[Label('Alert'),     Color('danger'),    Icon('alert-triangle'), Order(1)] case Alert = 1;
    #[Label('Critical'),  Color('danger'),    Icon('alert-octagon'),  Order(2)] case Critical = 2;
    #[Label('Error'),     Color('danger'),    Icon('x-octagon'),      Order(3)] case Error = 3;
    #[Label('Warning'),   Color('warning'),   Icon('triangle'),       Order(4)] case Warning = 4;
    #[Label('Notice'),    Color('info'),      Icon('info'),           Order(5)] case Notice = 5;
    #[Label('Info'),      Color('info'),      Icon('info'),           Order(6)] case Info = 6;
    #[Label('Debug'),     Color('secondary'), Icon('bug'),            Order(7)] case Debug = 7;
}
