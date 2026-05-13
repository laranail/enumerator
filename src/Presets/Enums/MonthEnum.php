<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Presets\Enums;

use Simtabi\Laranail\Enumerator\Attributes\Label;
use Simtabi\Laranail\Enumerator\Concerns\HasEnumeratorBehavior;
use Simtabi\Laranail\Enumerator\Concerns\HasLifecycle;
use Simtabi\Laranail\Enumerator\Concerns\HasOrder;
use Simtabi\Laranail\Enumerator\Contracts\Enumerator;

enum MonthEnum: int implements Enumerator
{
    use HasEnumeratorBehavior;
    use HasLifecycle;
    use HasOrder;

    #[Label('January')] case January = 1;
    #[Label('February')] case February = 2;
    #[Label('March')] case March = 3;
    #[Label('April')] case April = 4;
    #[Label('May')] case May = 5;
    #[Label('June')] case June = 6;
    #[Label('July')] case July = 7;
    #[Label('August')] case August = 8;
    #[Label('September')] case September = 9;
    #[Label('October')] case October = 10;
    #[Label('November')] case November = 11;
    #[Label('December')] case December = 12;
}
