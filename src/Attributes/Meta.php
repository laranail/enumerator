<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Attributes;

use Attribute;
use InvalidArgumentException;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_CLASS_CONSTANT | Attribute::IS_REPEATABLE)]
final readonly class Meta
{
    /** @var array<string, mixed> */
    public array $values;

    public function __construct(mixed ...$values)
    {
        $clean = [];
        foreach ($values as $key => $value) {
            if (! is_string($key)) {
                throw new InvalidArgumentException('Meta keys must be strings.');
            }
            $clean[$key] = $value;
        }
        $this->values = $clean;
    }
}
