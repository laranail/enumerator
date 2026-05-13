<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator;

use JsonSerializable;
use Simtabi\Laranail\Enumerator\Concerns\HasClassEnumBehavior;
use Simtabi\Laranail\Enumerator\Contracts\Enumerator;
use Stringable;

/**
 * Class-constant enumerator base. Intended for the rare scenarios where
 * native PHP 8.3+ enums aren't enough — mixed backing types within a single
 * enum, runtime-generated cases, or migration paths from legacy class-const
 * patterns.
 *
 * The class is intentionally empty: every method comes from
 * `Concerns\HasClassEnumBehavior`. Compose additional traits (e.g.
 * `HasBitmask`, `HasTransitions`) as needed.
 *
 * Prefer the native enum + `Concerns\HasEnumeratorBehavior` path for new
 * code.
 */
abstract class AbstractEnumeratorClass implements Enumerator, JsonSerializable, Stringable
{
    use HasClassEnumBehavior;
}
