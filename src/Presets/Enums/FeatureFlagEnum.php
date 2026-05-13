<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Presets\Enums;

use Simtabi\Laranail\Enumerator\Attributes\Bit;
use Simtabi\Laranail\Enumerator\Attributes\Label;
use Simtabi\Laranail\Enumerator\Concerns\HasBitmask;
use Simtabi\Laranail\Enumerator\Concerns\HasEnumeratorBehavior;
use Simtabi\Laranail\Enumerator\Contracts\Bitwise;
use Simtabi\Laranail\Enumerator\Contracts\Enumerator;

enum FeatureFlagEnum: string implements Bitwise, Enumerator
{
    use HasBitmask;
    use HasEnumeratorBehavior;

    #[Bit(1),  Label('Dark Mode')] case DarkMode = 'dark_mode';
    #[Bit(2),  Label('Beta UI')] case BetaUI = 'beta_ui';
    #[Bit(4),  Label('Experiments')] case Experiments = 'experiments';
    #[Bit(8),  Label('Telemetry')] case Telemetry = 'telemetry';
}
