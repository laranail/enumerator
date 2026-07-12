<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Presets\Enums;

use Simtabi\Laranail\Enumerator\Attributes\Label;
use Simtabi\Laranail\Enumerator\Concerns\HasEnumeratorBehavior;
use Simtabi\Laranail\Enumerator\Contracts\Enumerator;

enum RaceEnum: string implements Enumerator
{
    use HasEnumeratorBehavior;

    #[Label('American Indian or Alaska Native')] case AmericanIndianOrAlaskaNative = 'american_indian_or_alaska_native';
    #[Label('Asian')] case Asian = 'asian';
    #[Label('Black or African American')] case BlackOrAfricanAmerican = 'black_or_african_american';
    #[Label('Hispanic or Latino')] case HispanicOrLatino = 'hispanic_or_latino';
    #[Label('Native Hawaiian or Other Pacific Islander')] case NativeHawaiianOrOtherPacificIslander = 'native_hawaiian_or_other_pacific_islander';
    #[Label('White')] case White = 'white';
    #[Label('Two or More Races')] case TwoOrMoreRaces = 'two_or_more_races';
    #[Label('Other')] case Other = 'other';
    #[Label('Prefer not to say')] case PreferNotToSay = 'prefer_not_to_say';
}
