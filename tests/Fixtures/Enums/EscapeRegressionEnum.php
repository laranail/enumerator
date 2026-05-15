<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Tests\Fixtures\Enums;

use Simtabi\Laranail\Enumerator\Attributes\Color;
use Simtabi\Laranail\Enumerator\Attributes\Label;
use Simtabi\Laranail\Enumerator\Concerns\HasEnumerator;
use Simtabi\Laranail\Enumerator\Contracts\Enumerator;

/**
 * Fixture exercising RendersHtml::toHtml() escape behaviour on labels
 * containing HTML special characters. Lives only to support
 * RendersHtmlEscapeTest — should never be referenced from production
 * code paths.
 */
enum EscapeRegressionEnum: string implements Enumerator
{
    use HasEnumerator;

    #[Label('<b>bold</b>'), Color('primary')]
    case PlainHtml = 'plain_html';

    #[Label('A & B'), Color('primary')]
    case Ampersand = 'ampersand';

    #[Label('She said "hi"'), Color('primary')]
    case Quoted = 'quoted';

    #[Label('&lt;already-encoded&gt;'), Color('primary')]
    case PreEncoded = 'pre_encoded';
}
