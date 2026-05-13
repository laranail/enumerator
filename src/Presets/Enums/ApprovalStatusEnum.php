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

enum ApprovalStatusEnum: string implements Enumerator, Stateful
{
    use HasEnumeratorBehavior;
    use HasGrouping;
    use HasOrder;
    use HasTransitions;

    #[Label('Pending'),        Color('warning'),   Icon('clock'),         Order(10)] case Pending = 'pending';
    #[Label('Approved'),       Color('success'),   Icon('check-circle'),  Order(20)] case Approved = 'approved';
    #[Label('Rejected'),       Color('danger'),    Icon('x-circle'),      Order(30)] case Rejected = 'rejected';
    #[Label('Needs Revision'), Color('info'),      Icon('refresh-ccw'),   Order(40)] case NeedsRevision = 'needs_revision';
    #[Label('Cancelled'),      Color('secondary'), Icon('slash'),         Order(50)] case Cancelled = 'cancelled';

    public static function initialStates(): array
    {
        return [self::Pending];
    }

    public static function transitions(): array
    {
        return [
            self::Pending->value => [self::Approved, self::Rejected, self::NeedsRevision, self::Cancelled],
            self::Approved->value => [],
            self::Rejected->value => [self::Pending],
            self::NeedsRevision->value => [self::Pending, self::Cancelled],
            self::Cancelled->value => [],
        ];
    }

    public static function groups(): array
    {
        return [
            'positive' => [self::Approved],
            'negative' => [self::Rejected, self::Cancelled],
            'pending' => [self::Pending, self::NeedsRevision],
            'terminal' => [self::Approved, self::Cancelled],
        ];
    }
}
