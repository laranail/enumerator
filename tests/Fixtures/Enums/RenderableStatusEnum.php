<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Tests\Fixtures\Enums;

use Simtabi\Laranail\Enumerator\Attributes\Color;
use Simtabi\Laranail\Enumerator\Attributes\CssClass;
use Simtabi\Laranail\Enumerator\Attributes\Icon;
use Simtabi\Laranail\Enumerator\Attributes\Label;
use Simtabi\Laranail\Enumerator\Concerns\HasEnumerator;
use Simtabi\Laranail\Enumerator\Contracts\Enumerator;

/**
 * HTML-rendering fixture — full presentation attributes per case so we
 * can verify toHtml() output across all framework variants (plain /
 * tailwind / daisyui / bootstrap / bulma).
 */
enum RenderableStatusEnum: string implements Enumerator
{
    use HasEnumerator;

    #[
        Label('Active'),
        Color('success'),
        Icon('check-circle'),
        CssClass('badge bg-success', framework: 'bootstrap'),
        CssClass('badge badge-success', framework: 'daisyui'),
    ]
    case Active = 'active';

    #[
        Label('Inactive'),
        Color('ghost'),
        Icon('pause-circle'),
        CssClass('badge bg-secondary', framework: 'bootstrap'),
    ]
    case Inactive = 'inactive';

    #[
        Label('Banned'),
        Color('danger'),
        Icon('x-octagon'),
        CssClass('badge bg-danger', framework: 'bootstrap'),
    ]
    case Banned = 'banned';
}
