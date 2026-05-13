<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Presets\Enums;

use Simtabi\Laranail\Enumerator\Attributes\Color;
use Simtabi\Laranail\Enumerator\Attributes\Label;
use Simtabi\Laranail\Enumerator\Concerns\HasEnumeratorBehavior;
use Simtabi\Laranail\Enumerator\Contracts\Enumerator;

enum ToggleEnum: string implements Enumerator
{
    use HasEnumeratorBehavior;

    #[Label('On'),  Color('success')] case On = 'on';
    #[Label('Off'), Color('secondary')] case Off = 'off';

    public function isOn(): bool
    {
        return $this === self::On;
    }

    public function isOff(): bool
    {
        return $this === self::Off;
    }

    public function toBool(): bool
    {
        return $this === self::On;
    }
}
