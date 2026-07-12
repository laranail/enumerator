<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Simtabi\Laranail\Enumerator\AbstractEnumeratorClass;
use UnitEnum;

/**
 * Validate the value does NOT match any of the excluded cases.
 */
class EnumNotIn implements ValidationRule
{
    /**
     * @param  array<int, UnitEnum|AbstractEnumeratorClass>  $excluded
     */
    public function __construct(
        public readonly string $enumClass,
        public readonly array $excluded,
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        (new EnumValue($this->enumClass))
            ->except($this->excluded)
            ->validate($attribute, $value, $fail);
    }
}
