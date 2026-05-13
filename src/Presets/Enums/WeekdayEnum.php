<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Presets\Enums;

use InvalidArgumentException;
use Simtabi\Laranail\Enumerator\Attributes\Label;
use Simtabi\Laranail\Enumerator\Concerns\HasEnumeratorBehavior;
use Simtabi\Laranail\Enumerator\Concerns\HasLifecycle;
use Simtabi\Laranail\Enumerator\Concerns\HasOrder;
use Simtabi\Laranail\Enumerator\Contracts\Enumerator;

/**
 * Days of the week. Sunday-first by default (US convention); ISO-8601 and
 * Carbon helpers provided. Hydrate from any convention via fromNumber().
 */
enum WeekdayEnum: int implements Enumerator
{
    use HasEnumeratorBehavior;
    use HasLifecycle;
    use HasOrder;

    #[Label('Sunday')] case Sunday = 1;
    #[Label('Monday')] case Monday = 2;
    #[Label('Tuesday')] case Tuesday = 3;
    #[Label('Wednesday')] case Wednesday = 4;
    #[Label('Thursday')] case Thursday = 5;
    #[Label('Friday')] case Friday = 6;
    #[Label('Saturday')] case Saturday = 7;

    /** Sunday-first: Sun=1..Sat=7. */
    public function number(): int
    {
        return $this->value;
    }

    /** ISO-8601: Mon=1..Sun=7. */
    public function isoNumber(): int
    {
        return $this === self::Sunday ? 7 : $this->value - 1;
    }

    /** Carbon zero-based: Sun=0..Sat=6. */
    public function carbonIndex(): int
    {
        return $this->value - 1;
    }

    public function isWeekend(): bool
    {
        return in_array($this, [self::Sunday, self::Saturday], true);
    }

    public function isWeekday(): bool
    {
        return ! $this->isWeekend();
    }

    public static function fromNumber(int $n, string $convention = 'sunday-first'): self
    {
        return match ($convention) {
            'sunday-first' => self::from($n),
            'iso-8601' => $n === 7 ? self::Sunday : self::from($n + 1),
            'carbon' => self::from($n + 1),
            default => throw new InvalidArgumentException(sprintf('Unknown convention "%s".', $convention)),
        };
    }
}
