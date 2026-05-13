<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Presets\Enums;

use Simtabi\Laranail\Enumerator\Attributes\Label;
use Simtabi\Laranail\Enumerator\Attributes\Order;
use Simtabi\Laranail\Enumerator\Concerns\HasEnumeratorBehavior;
use Simtabi\Laranail\Enumerator\Concerns\HasLifecycle;
use Simtabi\Laranail\Enumerator\Concerns\HasOrder;
use Simtabi\Laranail\Enumerator\Contracts\Enumerator;

enum SizeEnum: string implements Enumerator
{
    use HasEnumeratorBehavior;
    use HasLifecycle;
    use HasOrder;

    #[Label('Extra Small'),         Order(10)] case XSmall = 'xs';
    #[Label('Small'),               Order(20)] case Small = 'sm';
    #[Label('Medium'),              Order(30)] case Medium = 'md';
    #[Label('Large'),               Order(40)] case Large = 'lg';
    #[Label('Extra Large'),         Order(50)] case XLarge = 'xl';
    #[Label('Extra Extra Large'),   Order(60)] case XXLarge = 'xxl';
}
