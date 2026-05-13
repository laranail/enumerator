<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Tests\Fixtures\Enums;

use Simtabi\Laranail\Enumerator\Attributes\Label;
use Simtabi\Laranail\Enumerator\Concerns\HasEnumerator;
use Simtabi\Laranail\Enumerator\Contracts\Enumerator;
use Simtabi\Laranail\Enumerator\Contracts\Translatable;

/**
 * Translatable fixture — overrides the default translation namespace
 * and slug so we can exercise the full Lang::has() → attribute → humanized
 * fallback chain in the i18n adapter tests.
 */
enum TranslatableStatusEnum: string implements Enumerator, Translatable
{
    use HasEnumerator;

    #[Label('Draft')] case Draft = 'draft';
    #[Label('Published')] case Published = 'published';
    #[Label('Archived')] case Archived = 'archived';

    public static function translationNamespace(): string
    {
        return 'enumerator-fixtures';
    }

    public static function translationSlug(): string
    {
        return 'translatable_status';
    }
}
