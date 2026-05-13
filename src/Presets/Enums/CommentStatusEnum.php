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

enum CommentStatusEnum: string implements Enumerator
{
    use HasEnumeratorBehavior;
    use HasGrouping;
    use HasOrder;

    #[Label('Pending'),  Color('warning'),   Icon('clock'),        Order(10)] case Pending = 'pending';
    #[Label('Approved'), Color('success'),   Icon('check-circle'), Order(20)] case Approved = 'approved';
    #[Label('Spam'),     Color('danger'),    Icon('alert-octagon'), Order(30)] case Spam = 'spam';
    #[Label('Trash'),    Color('secondary'), Icon('trash'),        Order(40)] case Trash = 'trash';

    public static function groups(): array
    {
        return [
            'positive' => [self::Approved],
            'negative' => [self::Spam, self::Trash],
            'pending' => [self::Pending],
        ];
    }
}
