<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Simtabi\Laranail\Enumerator\AbstractEnumeratorClass;
use Simtabi\Laranail\Enumerator\Contracts\Stateful;
use Simtabi\Laranail\Enumerator\Support\IsEnumeratorClass;

/**
 * Validate that the incoming value is a legal transition target from the
 * given source case (or null = initial state). Supports both native enums
 * and `AbstractEnumeratorClass` subclasses.
 *
 *   $request->validate([
 *       'status' => ['required', new EnumTransition(OrderStatusEnum::class, $order->status)],
 *   ]);
 */
class EnumTransition implements ValidationRule
{
    /**
     * @param  class-string<Stateful>  $enumClass
     */
    public function __construct(
        public readonly string $enumClass,
        public readonly ?object $from = null,
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $class = $this->enumClass;
        if (! IsEnumeratorClass::check($class) || ! is_subclass_of($class, Stateful::class)) {
            $fail(__('enumerator::enumerator.validation.invalid_enum_class', [
                'class' => $class,
            ]));

            return;
        }

        $target = $this->coerce($class, $value);
        if ($target === null) {
            $fail(__('enumerator::enumerator.validation.invalid_value', [
                'attribute' => $attribute,
                'enum' => class_basename($class),
            ]));

            return;
        }

        $targetName = $this->caseLabel($target);

        if ($this->from === null) {
            if (! in_array($target, $class::initialStates(), true)) {
                $fail(__('enumerator::enumerator.validation.invalid_transition', [
                    'from' => '(initial)',
                    'to' => $targetName,
                    'enum' => class_basename($class),
                ]));
            }

            return;
        }

        if (! method_exists($this->from, 'canTransitionTo')
            || ! $this->from->canTransitionTo($target)) {
            $fail(__('enumerator::enumerator.validation.invalid_transition', [
                'from' => $this->caseLabel($this->from),
                'to' => $targetName,
                'enum' => class_basename($class),
            ]));
        }
    }

    /**
     * Coerce the incoming raw value into a case instance, supporting both
     * native backed enums and class-const enums.
     *
     * @param  class-string  $class
     */
    private function coerce(string $class, mixed $value): ?object
    {
        if (! is_string($value) && ! is_int($value)) {
            return null;
        }

        if (enum_exists($class) && is_subclass_of($class, \BackedEnum::class)) {
            return $class::tryFrom($value);
        }

        if (is_subclass_of($class, AbstractEnumeratorClass::class)) {
            return $class::tryFromValue($value);
        }

        return null;
    }

    private function caseLabel(object $case): string
    {
        if ($case instanceof \UnitEnum) {
            return $case->name;
        }
        if ($case instanceof AbstractEnumeratorClass && method_exists($case, 'getKey')) {
            return (string) $case->getKey();
        }

        return $case::class;
    }
}
