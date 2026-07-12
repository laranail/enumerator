<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Presets\Enums;

use Simtabi\Laranail\Enumerator\Attributes\Bit;
use Simtabi\Laranail\Enumerator\Attributes\Label;
use Simtabi\Laranail\Enumerator\Concerns\HasBitmask;
use Simtabi\Laranail\Enumerator\Concerns\HasEnumeratorBehavior;
use Simtabi\Laranail\Enumerator\Contracts\Bitwise;
use Simtabi\Laranail\Enumerator\Contracts\Enumerator;

enum NotificationOptInEnum implements Bitwise, Enumerator
{
    use HasBitmask;
    use HasEnumeratorBehavior;

    #[Bit(1), Label('Email')] case Email;
    #[Bit(2), Label('SMS')] case SMS;
    #[Bit(4), Label('Push')] case Push;
    #[Bit(8), Label('Webhook')] case Webhook;
}
