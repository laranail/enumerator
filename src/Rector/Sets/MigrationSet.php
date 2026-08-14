<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Rector\Sets;

use Simtabi\Laranail\Enumerator\Rector\RectorBenSampoEnumToEnumerator;
use Simtabi\Laranail\Enumerator\Rector\RectorClassConstEnumToEnumerator;
use Simtabi\Laranail\Enumerator\Rector\RectorSpatieEnumToEnumerator;

/**
 * Rector rule-set descriptor.
 *
 * Consumers import this set in their `rector.php` to apply all
 * laranail/enumerator migration rules in one go:
 *
 *     use Rector\Config\RectorConfig;
 *     use Simtabi\Laranail\Enumerator\Rector\Sets\MigrationSet;
 *
 *     return RectorConfig::configure()->sets([MigrationSet::class]);
 *
 * Class is intentionally light — it exists so the FQCN can be passed
 * to Rector's `sets()` registrar. The bundled rules:
 *
 *   - `RectorBenSampoEnumToEnumerator` — BenSampo `Enum` subclasses → native enums
 *   - `RectorSpatieEnumToEnumerator` — Spatie `Enum` subclasses → native enums
 *   - `RectorClassConstEnumToEnumerator` — any bespoke class-constant enum base
 *     → `AbstractEnumeratorClass`. **Configurable**, so it does nothing until
 *     told which base classes to match; pass it through
 *     `withConfiguredRule()` rather than relying on this set.
 */
final class MigrationSet
{
    /**
     * The Rector rules this set applies.
     *
     * @return array<int, class-string>
     */
    public static function rules(): array
    {
        return [
            RectorBenSampoEnumToEnumerator::class,
            RectorSpatieEnumToEnumerator::class,
            RectorClassConstEnumToEnumerator::class,
        ];
    }
}
