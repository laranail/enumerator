<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS_CONSTANT)]
final readonly class Help
{
    public function __construct(
        public string $help,
    ) {}
}
