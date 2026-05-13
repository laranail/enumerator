<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS_CONSTANT | Attribute::IS_REPEATABLE)]
final readonly class CssClass
{
    public function __construct(
        public string $classes,
        public string $framework = 'plain',
    ) {}
}
