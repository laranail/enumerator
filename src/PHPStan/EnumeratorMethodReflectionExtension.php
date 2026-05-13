<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\PHPStan;

use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\MethodsClassReflectionExtension;
use Simtabi\Laranail\Enumerator\Contracts\Enumerator;

/**
 * PHPStan extension: silences "undefined method" for the dynamic helpers
 * shipped by `HasMagicComparisons` (`isActive()`, `isNotBanned()`, etc.) and
 * `HasInvokableCases` static call style. Full reflection logic lives in
 * Larastan's framework helpers; this is a permissive shim that
 * accepts any `is.*` / `isNot.*` zero-arg call on an Enumerator.
 */
final class EnumeratorMethodReflectionExtension implements MethodsClassReflectionExtension
{
    public function hasMethod(ClassReflection $classReflection, string $methodName): bool
    {
        if (! $classReflection->implementsInterface(Enumerator::class)) {
            return false;
        }

        return str_starts_with($methodName, 'is');
    }

    public function getMethod(ClassReflection $classReflection, string $methodName): MethodReflection
    {
        return new EnumeratorReflectionExtension($classReflection, $methodName);
    }
}
