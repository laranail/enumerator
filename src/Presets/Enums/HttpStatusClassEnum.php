<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Presets\Enums;

use Simtabi\Laranail\Enumerator\Attributes\Color;
use Simtabi\Laranail\Enumerator\Attributes\Label;
use Simtabi\Laranail\Enumerator\Attributes\Order;
use Simtabi\Laranail\Enumerator\Concerns\HasEnumeratorBehavior;
use Simtabi\Laranail\Enumerator\Concerns\HasOrder;
use Simtabi\Laranail\Enumerator\Contracts\Enumerator;

enum HttpStatusClassEnum: int implements Enumerator
{
    use HasEnumeratorBehavior;
    use HasOrder;

    #[Label('Informational'), Color('info'),    Order(1)] case Informational = 1;
    #[Label('Success'),       Color('success'), Order(2)] case Success = 2;
    #[Label('Redirection'),   Color('warning'), Order(3)] case Redirection = 3;
    #[Label('Client Error'),  Color('danger'),  Order(4)] case ClientError = 4;
    #[Label('Server Error'),  Color('danger'),  Order(5)] case ServerError = 5;

    public static function fromStatus(int $status): self
    {
        return self::from(intdiv($status, 100));
    }

    public function contains(int $status): bool
    {
        return intdiv($status, 100) === $this->value;
    }
}
