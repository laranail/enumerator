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

enum PaymentStatusEnum: string implements Enumerator, Stateful
{
    use HasEnumeratorBehavior;
    use HasGrouping;
    use HasOrder;
    use HasTransitions;

    #[Label('Pending'),    Color('warning'),   Icon('clock'),       Order(10)] case Pending = 'pending';
    #[Label('Authorized'), Color('info'),      Icon('shield'),      Order(20)] case Authorized = 'authorized';
    #[Label('Captured'),   Color('success'),   Icon('check'),       Order(30)] case Captured = 'captured';
    #[Label('Failed'),     Color('danger'),    Icon('alert-octagon'), Order(40)] case Failed = 'failed';
    #[Label('Refunded'),   Color('secondary'), Icon('rotate-ccw'),  Order(50)] case Refunded = 'refunded';
    #[Label('Voided'),     Color('secondary'), Icon('slash'),       Order(60)] case Voided = 'voided';
    #[Label('Disputed'),   Color('danger'),    Icon('alert-circle'), Order(70)] case Disputed = 'disputed';

    public static function initialStates(): array
    {
        return [self::Pending];
    }

    public static function transitions(): array
    {
        return [
            self::Pending->value => [self::Authorized, self::Failed, self::Voided],
            self::Authorized->value => [self::Captured, self::Voided, self::Failed],
            self::Captured->value => [self::Refunded, self::Disputed],
            self::Failed->value => [],
            self::Refunded->value => [],
            self::Voided->value => [],
            self::Disputed->value => [self::Refunded, self::Captured],
        ];
    }

    public static function groups(): array
    {
        return [
            'positive' => [self::Captured],
            'negative' => [self::Failed, self::Voided, self::Refunded, self::Disputed],
            'pending' => [self::Pending, self::Authorized],
            'terminal' => [self::Failed, self::Voided, self::Refunded],
        ];
    }
}
