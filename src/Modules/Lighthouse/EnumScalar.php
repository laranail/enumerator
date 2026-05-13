<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Modules\Lighthouse;

use GraphQL\Error\Error;
use GraphQL\Language\AST\Node;
use GraphQL\Type\Definition\ScalarType;
use Simtabi\Laranail\Enumerator\AbstractEnumeratorClass;
use Simtabi\Laranail\Enumerator\Support\IsEnumeratorClass;
use UnitEnum;

// Lighthouse depends on webonyx/graphql-php. If neither is installed,
// this file early-returns so the autoloader doesn't try to extend a
// non-existent ScalarType base class.
if (! class_exists(ScalarType::class)) {
    return;
}

/**
 * Lighthouse-compatible GraphQL scalar that round-trips enumerator
 * cases through the schema. *
 * Distinct from `Modules/GraphQL/SchemaExporter` (which emits schema
 * text). This is the **runtime resolver** — parses incoming values into
 * case instances and serializes case instances back to scalars.
 *
 * Consumer wires one of these per enum:
 *
 *     scalar UserStatus @scalar(class: "App\\GraphQL\\UserStatusScalar")
 *
 *     class UserStatusScalar extends EnumScalar
 *     {
 *         public string $name = 'UserStatus';
 *         protected string $enumClass = UserStatusEnum::class;
 *     }
 */
abstract class EnumScalar extends ScalarType
{
    /**
     * Override in subclasses to point at the wrapped enum class.
     *
     * @var class-string
     */
    protected string $enumClass = '';

    /**
     * Serialize an internal case instance to a GraphQL scalar value.
     */
    public function serialize(mixed $value): mixed
    {
        if ($value instanceof UnitEnum || $value instanceof AbstractEnumeratorClass) {
            return IsEnumeratorClass::valueOf($value);
        }

        // Already a scalar — pass through. Useful when GraphQL framework
        // hands back already-serialized values.
        return $value;
    }

    /**
     * Parse a value from a GraphQL variable (request payload).
     */
    public function parseValue(mixed $value): mixed
    {
        return $this->coerce($value);
    }

    /**
     * Parse a value from an inline GraphQL literal in a query document.
     *
     * @param  array<string, mixed>|null  $variables
     */
    public function parseLiteral(Node $valueNode, ?array $variables = null): mixed
    {
        unset($variables);
        /** @var string|int|null $raw */
        $raw = property_exists($valueNode, 'value') ? $valueNode->value : null;

        return $this->coerce($raw);
    }

    /**
     * Coerce a scalar into an enum case instance.
     */
    private function coerce(mixed $value): mixed
    {
        if ($value === null) {
            return null;
        }
        if (! is_string($value) && ! is_int($value)) {
            throw new Error(sprintf(
                'Cannot resolve %s from %s.',
                $this->enumClass !== '' ? $this->enumClass : static::class,
                get_debug_type($value),
            ));
        }
        if ($this->enumClass === '' || ! IsEnumeratorClass::check($this->enumClass)) {
            throw new Error(sprintf(
                'EnumScalar subclass %s has not declared a valid $enumClass.',
                static::class,
            ));
        }

        $class = $this->enumClass;
        if (enum_exists($class) && is_subclass_of($class, \BackedEnum::class)) {
            $case = $class::tryFrom($value);
            if ($case !== null) {
                return $case;
            }
        }
        if (is_string($value) && method_exists($class, 'tryFromName')) {
            $case = $class::tryFromName($value);
            if ($case !== null) {
                return $case;
            }
        }
        if (is_subclass_of($class, AbstractEnumeratorClass::class)) {
            $case = $class::tryFromValue($value);
            if ($case !== null) {
                return $case;
            }
        }

        throw new Error(sprintf('Value %s is not a valid case of %s.', var_export($value, true), $class));
    }
}
