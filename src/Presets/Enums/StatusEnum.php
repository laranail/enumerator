<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Presets\Enums;

use Simtabi\Laranail\Enumerator\Attributes\Color;
use Simtabi\Laranail\Enumerator\Attributes\Icon;
use Simtabi\Laranail\Enumerator\Attributes\Label;
use Simtabi\Laranail\Enumerator\Attributes\Order;
use Simtabi\Laranail\Enumerator\Concerns\HasEnumeratorBehavior;
use Simtabi\Laranail\Enumerator\Concerns\HasGrouping;
use Simtabi\Laranail\Enumerator\Concerns\HasOrder;
use Simtabi\Laranail\Enumerator\Contracts\Enumerator;

enum StatusEnum: string implements Enumerator
{
    use HasEnumeratorBehavior;
    use HasGrouping;
    use HasOrder;

    #[Label('Active'),   Color('success'),   Icon('check-circle'), Order(10)] case Active = 'active';
    #[Label('Inactive'), Color('ghost'),     Icon('pause-circle'), Order(20)] case Inactive = 'inactive';
    #[Label('Pending'),  Color('warning'),   Icon('clock'),        Order(30)] case Pending = 'pending';
    #[Label('Archived'), Color('secondary'), Icon('archive'),      Order(40)] case Archived = 'archived';

    public static function groups(): array
    {
        return [
            'positive' => [self::Active],
            'negative' => [self::Archived],
            'pending' => [self::Pending],
        ];
    }
}
