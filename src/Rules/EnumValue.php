<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Rules;

use BackedEnum;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Simtabi\Laranail\Enumerator\AbstractEnumeratorClass;
use Simtabi\Laranail\Enumerator\Contracts\Enumerator;
use UnitEnum;

/**
 * Validate that an attribute is a valid backing value of the given enum.
 *
 *   'status' => ['required', new EnumValue(UserStatusEnum::class)],
 *   'status' => ['required', EnumValue::for(UserStatusEnum::class)->only([UserStatusEnum::Active])],
 */
class EnumValue implements ValidationRule
{
    /** @var array<int, UnitEnum|AbstractEnumeratorClass>|null */
    protected ?array $only = null;

    /** @var array<int, UnitEnum|AbstractEnumeratorClass>|null */
    protected ?array $except = null;

    /**
     * @param  class-string<Enumerator>  $enumClass
     */
    public function __construct(public readonly string $enumClass) {}

    public static function for(string $enumClass): static
    {
        return new static($enumClass);
    }

    /**
     * @param  array<int, UnitEnum|AbstractEnumeratorClass>  $cases
     */
    public function only(array $cases): static
    {
        $this->only = $cases;

        return $this;
    }

    /**
     * @param  array<int, UnitEnum|AbstractEnumeratorClass>  $cases
     */
    public function except(array $cases): static
    {
        $this->except = $cases;

        return $this;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $case = $this->coerce($value);
        if ($case === null) {
            $fail(__('enumerator::enumerator.validation.invalid_value', [
                'attribute' => $attribute,
                'enum' => class_basename($this->enumClass),
            ]));

            return;
        }

        if ($this->only !== null && ! in_array($case, $this->only, true)) {
            $fail(__('enumerator::enumerator.validation.not_allowed', [
                'attribute' => $attribute,
                'values' => $this->valuesAsString($this->only),
            ]));

            return;
        }

        if ($this->except !== null && in_array($case, $this->except, true)) {
            $fail(__('enumerator::enumerator.validation.excluded', [
                'attribute' => $attribute,
                'value' => (string) $value,
            ]));
        }
    }

    private function coerce(mixed $value): ?object
    {
        if ($value === null || $value === '') {
            return null;
        }
        $class = $this->enumClass;

        if (enum_exists($class)) {
            if (is_subclass_of($class, BackedEnum::class) && (is_string($value) || is_int($value))) {
                return $class::tryFrom($value);
            }

            // pure enums (no backing type) fall through to name
            // lookup when HasFromHelpers is mixed in.
            if (is_string($value) && method_exists($class, 'tryFromName')) {
                return $class::tryFromName($value);
            }

            return null;
        }

        /** @var class-string<AbstractEnumeratorClass> $class */
        if (is_string($value) || is_int($value)) {
            return $class::tryFromValue($value);
        }

        return null;
    }

    /**
     * @param  array<int, UnitEnum|AbstractEnumeratorClass>  $cases
     */
    private function valuesAsString(array $cases): string
    {
        return implode(', ', array_map(static function (object $case): string {
            if ($case instanceof BackedEnum) {
                return (string) $case->value;
            }
            if ($case instanceof UnitEnum) {
                return $case->name;
            }
            if ($case instanceof AbstractEnumeratorClass) {
                return (string) $case->getValue();
            }

            return $case::class;
        }, $cases));
    }
}
