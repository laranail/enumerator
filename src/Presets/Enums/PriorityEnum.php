<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Presets\Enums;

use Simtabi\Laranail\Enumerator\Attributes\Color;
use Simtabi\Laranail\Enumerator\Attributes\Icon;
use Simtabi\Laranail\Enumerator\Attributes\Label;
use Simtabi\Laranail\Enumerator\Attributes\Order;
use Simtabi\Laranail\Enumerator\Concerns\HasEnumeratorBehavior;
use Simtabi\Laranail\Enumerator\Concerns\HasGrouping;
use Simtabi\Laranail\Enumerator\Concerns\HasLifecycle;
use Simtabi\Laranail\Enumerator\Concerns\HasOrder;
use Simtabi\Laranail\Enumerator\Contracts\Enumerator;

enum PriorityEnum: string implements Enumerator
{
    use HasEnumeratorBehavior;
    use HasGrouping;
    use HasLifecycle;
    use HasOrder;

    #[Label('Low'),      Color('secondary'), Icon('arrow-down'),       Order(10)] case Low = 'low';
    #[Label('Medium'),   Color('info'),      Icon('minus'),            Order(20)] case Medium = 'medium';
    #[Label('High'),     Color('warning'),   Icon('arrow-up'),         Order(30)] case High = 'high';
    #[Label('Urgent'),   Color('danger'),    Icon('zap'),              Order(40)] case Urgent = 'urgent';
    #[Label('Critical'), Color('danger'),    Icon('alert-octagon'),    Order(50)] case Critical = 'critical';

    public static function groups(): array
    {
        return [
            'high' => [self::High, self::Urgent, self::Critical],
            'low' => [self::Low, self::Medium],
        ];
    }
}
