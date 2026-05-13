<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Routing;

use BackedEnum;
use Illuminate\Support\Facades\Route;
use Simtabi\Laranail\Enumerator\AbstractEnumeratorClass;
use Simtabi\Laranail\Enumerator\Contracts\Enumerator;
use Simtabi\Laranail\Enumerator\Support\IsEnumeratorClass;

/**
 * Adds a case-insensitive route binder for any enumerator class plus an
 * optional fallback case for invalid URL params. Supports both native
 * enums (backed and pure) and `AbstractEnumeratorClass` subclasses
 *.
 *
 * Usage from the service provider boot():
 *
 *     EnumeratorRouteBinder::register('status', UserStatusEnum::class);
 *     // or with fallback:
 *     EnumeratorRouteBinder::register('status', UserStatusEnum::class, UserStatusEnum::Inactive);
 *
 * Then a route binding `{status}` resolves to the enum case.
 */
final class EnumeratorRouteBinder
{
    /**
     * @param  class-string<Enumerator>  $enumClass
     */
    public static function register(string $param, string $enumClass, ?object $fallback = null): void
    {
        Route::bind($param, static function (string $value) use ($enumClass, $fallback): object {
            if (! IsEnumeratorClass::check($enumClass)) {
                abort(404);
            }

            // Native enum path
            if (enum_exists($enumClass)) {
                if (is_subclass_of($enumClass, BackedEnum::class)) {
                    $case = $enumClass::tryFrom($value);
                    if ($case !== null) {
                        return $case;
                    }
                    // Case-insensitive scan
                    foreach ($enumClass::cases() as $candidate) {
                        if (strcasecmp((string) $candidate->value, $value) === 0) {
                            return $candidate;
                        }
                    }
                }

                if (method_exists($enumClass, 'tryFromName')) {
                    $case = $enumClass::tryFromName($value);
                    if ($case !== null) {
                        return $case;
                    }
                }
            }

            // Class-const path (AbstractEnumeratorClass subclass)
            if (is_subclass_of($enumClass, AbstractEnumeratorClass::class)) {
                $case = $enumClass::tryFromValue($value);
                if ($case !== null) {
                    return $case;
                }
                // Case-insensitive value scan
                foreach ($enumClass::cases() as $candidate) {
                    if (strcasecmp((string) $candidate->getValue(), $value) === 0) {
                        return $candidate;
                    }
                }
                $case = $enumClass::tryFromKey($value);
                if ($case !== null) {
                    return $case;
                }
            }

            if ($fallback !== null) {
                return $fallback;
            }

            abort(404);
        });
    }
}
