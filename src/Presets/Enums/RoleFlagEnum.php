<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Presets\Enums;

use Simtabi\Laranail\Enumerator\Attributes\Bit;
use Simtabi\Laranail\Enumerator\Attributes\Label;
use Simtabi\Laranail\Enumerator\Concerns\HasBitmask;
use Simtabi\Laranail\Enumerator\Concerns\HasEnumeratorBehavior;
use Simtabi\Laranail\Enumerator\Contracts\Bitwise;
use Simtabi\Laranail\Enumerator\Contracts\Enumerator;

/**
 * Role-based access flags as a bitmask. Combine roles via `RoleFlagEnum::mask(...)`
 * and store the int via `Casts\AsBitmask::of(RoleFlagEnum::class)`.
 *
 * Migrated from the legacy class-const path (`AbstractEnumeratorClass`) to a
 * native PHP 8.3+ enum so all framework features (`HasBitmask`, native
 * `cases()`, `tryFrom()`) work out of the box.
 */
enum RoleFlagEnum: string implements Bitwise, Enumerator
{
    use HasBitmask;
    use HasEnumeratorBehavior;

    #[Bit(1), Label('Subscriber')]
    case Subscriber = 'subscriber';

    #[Bit(2), Label('Contributor')]
    case Contributor = 'contributor';

    #[Bit(4), Label('Editor')]
    case Editor = 'editor';

    #[Bit(8), Label('Admin')]
    case Admin = 'admin';
}
