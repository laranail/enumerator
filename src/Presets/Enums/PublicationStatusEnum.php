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
use Simtabi\Laranail\Enumerator\Concerns\HasTransitions;
use Simtabi\Laranail\Enumerator\Contracts\Enumerator;
use Simtabi\Laranail\Enumerator\Contracts\Stateful;

enum PublicationStatusEnum: string implements Enumerator, Stateful
{
    use HasEnumeratorBehavior;
    use HasGrouping;
    use HasLifecycle;
    use HasOrder;
    use HasTransitions;

    #[Label('Draft'),     Color('secondary'), Icon('pencil'),       Order(10)] case Draft = 'draft';
    #[Label('Pending'),   Color('warning'),   Icon('clock'),        Order(20)] case Pending = 'pending';
    #[Label('Published'), Color('success'),   Icon('check-circle'), Order(30)] case Published = 'published';
    #[Label('Archived'),  Color('secondary'), Icon('archive'),      Order(40)] case Archived = 'archived';
    #[Label('Deleted'),   Color('danger'),    Icon('trash'),        Order(50)] case Deleted = 'deleted';

    public static function initialStates(): array
    {
        return [self::Draft];
    }

    public static function transitions(): array
    {
        return [
            self::Draft->value => [self::Pending, self::Archived],
            self::Pending->value => [self::Published, self::Draft, self::Archived],
            self::Published->value => [self::Archived],
            self::Archived->value => [self::Deleted, self::Draft],
            self::Deleted->value => [],
        ];
    }

    public static function groups(): array
    {
        return [
            'positive' => [self::Published],
            'negative' => [self::Deleted],
            'pending' => [self::Draft, self::Pending],
            'terminal' => [self::Deleted],
        ];
    }
}
