<?php

declare(strict_types=1);

namespace Acme\Enums;

use Simtabi\Laranail\Enumerator\Tests\Fixtures\Rector\LegacyBase;

class Currency extends LegacyBase
{
    public const USD = 'USD';

    public const KES = 'KES';

    protected static string $langPath = 'acme::enums.currency';

    public function locale(): string
    {
        return match ($this->value) {
            self::USD => 'en_US',
            self::KES => 'en_GB',
            default => 'en_US',
        };
    }

    public static function pick(string $code): self
    {
        return self::from($code);
    }

    public static function maybe(string $code): ?self
    {
        return self::tryFrom($code);
    }

    public static function build(string $code): self
    {
        return static::of($code);
    }

    public function isSame(Currency $other): bool
    {
        return $this->value === $other->getValue();
    }
}
