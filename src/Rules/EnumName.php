<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Simtabi\Laranail\Enumerator\AbstractEnumeratorClass;
use Simtabi\Laranail\Enumerator\Contracts\Enumerator;

/**
 * Validate that an attribute is a valid case name on the enum (e.g.
 * "Active", "ACTIVE"). Useful for APIs that ship the name rather than the
 * backed value.
 */
class EnumName implements ValidationRule
{
    /**
     * @param  class-string<Enumerator>  $enumClass
     */
    public function __construct(public readonly string $enumClass) {}

    public static function for(string $enumClass): static
    {
        return new static($enumClass);
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)) {
            $fail(__('enumerator::enumerator.validation.invalid_name', [
                'attribute' => $attribute,
                'enum' => class_basename($this->enumClass),
            ]));

            return;
        }

        $class = $this->enumClass;
        if (enum_exists($class)) {
            if (method_exists($class, 'tryFromName')) {
                if ($class::tryFromName($value) === null) {
                    $this->failName($attribute, $fail);
                }

                return;
            }
        }

        /** @var class-string<AbstractEnumeratorClass> $class */
        if (is_subclass_of($class, AbstractEnumeratorClass::class)) {
            if ($class::tryFromKey($value) === null) {
                $this->failName($attribute, $fail);
            }

            return;
        }

        $this->failName($attribute, $fail);
    }

    private function failName(string $attribute, Closure $fail): void
    {
        $fail(__('enumerator::enumerator.validation.invalid_name', [
            'attribute' => $attribute,
            'enum' => class_basename($this->enumClass),
        ]));
    }
}
