<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Contracts;

use Illuminate\Support\HtmlString;

interface HtmlRenderable
{
    public function toHtml(): HtmlString;
}
