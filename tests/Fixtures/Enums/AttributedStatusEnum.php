<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Tests\Fixtures\Enums;

use Simtabi\Laranail\Enumerator\Attributes\Color;
use Simtabi\Laranail\Enumerator\Attributes\Description;
use Simtabi\Laranail\Enumerator\Attributes\Icon;
use Simtabi\Laranail\Enumerator\Attributes\Label;
use Simtabi\Laranail\Enumerator\Attributes\Meta;
use Simtabi\Laranail\Enumerator\Attributes\Order;
use Simtabi\Laranail\Enumerator\Concerns\HasEnumeratorBehavior;
use Simtabi\Laranail\Enumerator\Contracts\Enumerator;

#[Description('Attributed status fixture')]
enum AttributedStatusEnum: string implements Enumerator
{
    use HasEnumeratorBehavior;

    #[Label('Active'), Color('success'), Icon('check'), Order(10), Meta(notify: true)]
    case Active = 'active';

    #[Label('Banned'), Color('danger'), Icon('x'), Order(20), Meta(notify: false, audit: true)]
    case Banned = 'banned';
}
