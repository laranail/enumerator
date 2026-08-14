<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Tests\Fixtures\Enums;

use Simtabi\Laranail\Enumerator\AbstractEnumeratorClass;
use Simtabi\Laranail\Enumerator\Attributes\Label;
use Simtabi\Laranail\Enumerator\Contracts\Translatable;

/**
 * A class-constant enum that came off a legacy base carrying a translation
 * namespace, shaped the way `RectorClassConstEnumToEnumerator` leaves one.
 *
 * The legacy `$langPath = 'modules/core::enums.currency'` decomposes exactly
 * into the two overrides below, which is what makes the migration lossless.
 */
final class TranslatableLegacyCurrency extends AbstractEnumeratorClass implements Translatable
{
    #[Label('US Dollar')]
    public const USD = 'USD';

    public const KES = 'KES';

    public static function translationNamespace(): string
    {
        return 'modules/core';
    }

    public static function translationSlug(): string
    {
        return 'currency';
    }
}
