<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Tests\Fixtures\Enums;

use Simtabi\Laranail\Enumerator\Attributes\Label;
use Simtabi\Laranail\Enumerator\Attributes\Order;
use Simtabi\Laranail\Enumerator\Concerns\HasEnumerator;
use Simtabi\Laranail\Enumerator\Contracts\Enumerator;

/**
 * Order-attributed fixture — declaration order does NOT match logical
 * order; the `#[Order]` attribute drives sortedByOrder() / next() / previous().
 */
enum OrderedStatusEnum: string implements Enumerator
{
    use HasEnumerator;

    // declaration order intentionally scrambled
    #[Label('Third'),  Order(30)] case Third = 'third';
    #[Label('First'),  Order(10)] case First = 'first';
    #[Label('Fourth'), Order(40)] case Fourth = 'fourth';
    #[Label('Second'), Order(20)] case Second = 'second';
}
