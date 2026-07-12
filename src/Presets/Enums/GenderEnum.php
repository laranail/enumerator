<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Presets\Enums;

use Simtabi\Laranail\Enumerator\Attributes\Label;
use Simtabi\Laranail\Enumerator\Concerns\HasEnumeratorBehavior;
use Simtabi\Laranail\Enumerator\Contracts\Enumerator;

enum GenderEnum: string implements Enumerator
{
    use HasEnumeratorBehavior;

    #[Label('Female')] case Female = 'female';
    #[Label('Male')] case Male = 'male';
    #[Label('Non-binary')] case NonBinary = 'non_binary';
    #[Label('Other')] case Other = 'other';
    #[Label('Prefer not to say')] case PreferNotToSay = 'prefer_not_to_say';
}
