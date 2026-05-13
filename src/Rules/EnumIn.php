<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Simtabi\Laranail\Enumerator\AbstractEnumeratorClass;
use UnitEnum;

/**
 * Validate the value matches one of an explicit allow-list of cases.
 * Thin wrapper around EnumValue::only().
 */
class EnumIn implements ValidationRule
{
    /**
     * @param  array<int, UnitEnum|AbstractEnumeratorClass>  $allowed
     */
    public function __construct(
        public readonly string $enumClass,
        public readonly array $allowed,
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        (new EnumValue($this->enumClass))
            ->only($this->allowed)
            ->validate($attribute, $value, $fail);
    }
}
