<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Presets\Enums;

use Simtabi\Laranail\Enumerator\Attributes\Label;
use Simtabi\Laranail\Enumerator\Concerns\HasEnumeratorBehavior;
use Simtabi\Laranail\Enumerator\Contracts\Enumerator;

enum MaritalStatusEnum: string implements Enumerator
{
    use HasEnumeratorBehavior;

    #[Label('Single')] case Single = 'single';
    #[Label('Married')] case Married = 'married';
    #[Label('Divorced')] case Divorced = 'divorced';
    #[Label('Widowed')] case Widowed = 'widowed';
    #[Label('Separated')] case Separated = 'separated';
    #[Label('Domestic Partnership')] case DomesticPartnership = 'domestic_partnership';
    #[Label('Other')] case Other = 'other';
}
