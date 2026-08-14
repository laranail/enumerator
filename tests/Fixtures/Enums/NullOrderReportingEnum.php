<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Tests\Fixtures\Enums;

use Simtabi\Laranail\Enumerator\Attributes\Label;
use Simtabi\Laranail\Enumerator\Attributes\Order;

/**
 * A foreign enum that has a `getOrder()` but cannot answer with one.
 *
 * Deliberately does NOT use `HasOrder` — the point is what happens when
 * `compareTo()` is handed a case from somewhere else. It probes with
 * `method_exists()`, which proves the method is callable and says nothing about
 * what it returns.
 *
 * The old code read that as `(int) $other->getOrder()`. `(int) null` is 0, so
 * this enum sorted *before* everything — the opposite of the documented default,
 * where a case with no usable order sorts last. The `#[Order]` attributes below
 * are what the fallback should find instead.
 */
enum NullOrderReportingEnum: string
{
    #[Label('Early'), Order(10)] case Early = 'early';
    #[Label('Late'), Order(90)] case Late = 'late';

    public function getOrder(): null
    {
        return null;
    }
}
