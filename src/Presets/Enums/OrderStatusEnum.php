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
use Simtabi\Laranail\Enumerator\Concerns\HasTransitions;
use Simtabi\Laranail\Enumerator\Contracts\Enumerator;
use Simtabi\Laranail\Enumerator\Contracts\Stateful;

enum OrderStatusEnum: string implements Enumerator, Stateful
{
    use HasEnumeratorBehavior;
    use HasGrouping;
    use HasOrder;
    use HasTransitions;

    #[Label('Pending'),    Color('warning'),   Icon('clock'),        Order(10)] case Pending = 'pending';
    #[Label('Confirmed'),  Color('info'),      Icon('check'),        Order(20)] case Confirmed = 'confirmed';
    #[Label('Processing'), Color('info'),      Icon('refresh-ccw'),  Order(30)] case Processing = 'processing';
    #[Label('Shipped'),    Color('primary'),   Icon('truck'),        Order(40)] case Shipped = 'shipped';
    #[Label('Delivered'),  Color('success'),   Icon('check-circle'), Order(50)] case Delivered = 'delivered';
    #[Label('Cancelled'),  Color('secondary'), Icon('x-circle'),     Order(60)] case Cancelled = 'cancelled';
    #[Label('Refunded'),   Color('danger'),    Icon('rotate-ccw'),   Order(70)] case Refunded = 'refunded';

    public static function initialStates(): array
    {
        return [self::Pending];
    }

    public static function transitions(): array
    {
        return [
            self::Pending->value => [self::Confirmed, self::Cancelled],
            self::Confirmed->value => [self::Processing, self::Cancelled],
            self::Processing->value => [self::Shipped, self::Cancelled],
            self::Shipped->value => [self::Delivered, self::Refunded],
            self::Delivered->value => [self::Refunded],
            self::Cancelled->value => [],
            self::Refunded->value => [],
        ];
    }

    public static function groups(): array
    {
        return [
            'positive' => [self::Delivered],
            'negative' => [self::Cancelled, self::Refunded],
            'pending' => [self::Pending, self::Confirmed, self::Processing, self::Shipped],
            'terminal' => [self::Delivered, self::Cancelled, self::Refunded],
        ];
    }
}
