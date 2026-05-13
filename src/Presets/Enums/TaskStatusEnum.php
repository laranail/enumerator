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

enum TaskStatusEnum: string implements Enumerator, Stateful
{
    use HasEnumeratorBehavior;
    use HasGrouping;
    use HasOrder;
    use HasTransitions;

    #[Label('To Do'),       Color('secondary'), Icon('list'),         Order(10)] case ToDo = 'to_do';
    #[Label('In Progress'), Color('info'),      Icon('refresh-ccw'),  Order(20)] case InProgress = 'in_progress';
    #[Label('In Review'),   Color('warning'),   Icon('eye'),          Order(30)] case InReview = 'in_review';
    #[Label('Done'),        Color('success'),   Icon('check-circle'), Order(40)] case Done = 'done';
    #[Label('Cancelled'),   Color('secondary'), Icon('x-circle'),     Order(50)] case Cancelled = 'cancelled';
    #[Label('Blocked'),     Color('danger'),    Icon('alert-circle'), Order(60)] case Blocked = 'blocked';

    public static function initialStates(): array
    {
        return [self::ToDo];
    }

    public static function transitions(): array
    {
        return [
            self::ToDo->value => [self::InProgress, self::Cancelled, self::Blocked],
            self::InProgress->value => [self::InReview, self::Done, self::Blocked, self::Cancelled],
            self::InReview->value => [self::Done, self::InProgress, self::Blocked],
            self::Done->value => [],
            self::Cancelled->value => [],
            self::Blocked->value => [self::ToDo, self::InProgress, self::Cancelled],
        ];
    }

    public static function groups(): array
    {
        return [
            'positive' => [self::Done],
            'negative' => [self::Cancelled, self::Blocked],
            'pending' => [self::ToDo, self::InProgress, self::InReview],
            'terminal' => [self::Done, self::Cancelled],
        ];
    }
}
