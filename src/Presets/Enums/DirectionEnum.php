<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Presets\Enums;

use Simtabi\Laranail\Enumerator\Attributes\Icon;
use Simtabi\Laranail\Enumerator\Attributes\Label;
use Simtabi\Laranail\Enumerator\Concerns\HasEnumeratorBehavior;
use Simtabi\Laranail\Enumerator\Contracts\Enumerator;

enum DirectionEnum: string implements Enumerator
{
    use HasEnumeratorBehavior;

    #[Label('Ascending'),  Icon('arrow-up')] case Ascending = 'asc';
    #[Label('Descending'), Icon('arrow-down')] case Descending = 'desc';
}
