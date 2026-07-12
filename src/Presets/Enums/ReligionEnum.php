<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Presets\Enums;

use Simtabi\Laranail\Enumerator\Attributes\Label;
use Simtabi\Laranail\Enumerator\Concerns\HasEnumeratorBehavior;
use Simtabi\Laranail\Enumerator\Contracts\Enumerator;

enum ReligionEnum: string implements Enumerator
{
    use HasEnumeratorBehavior;

    #[Label('Christianity')] case Christianity = 'christianity';
    #[Label('Islam')] case Islam = 'islam';
    #[Label('Hinduism')] case Hinduism = 'hinduism';
    #[Label('Buddhism')] case Buddhism = 'buddhism';
    #[Label('Judaism')] case Judaism = 'judaism';
    #[Label('Sikhism')] case Sikhism = 'sikhism';
    #[Label('Folk Religion')] case FolkReligion = 'folk_religion';
    #[Label('No Religion')] case NoReligion = 'no_religion';
    #[Label('Other')] case Other = 'other';
    #[Label('Prefer not to say')] case PreferNotToSay = 'prefer_not_to_say';
}
