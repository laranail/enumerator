<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Facades;

use Illuminate\Support\Facades\Facade;
use Simtabi\Laranail\Enumerator\Support\EnumeratorRegistry;

/**
 * @method static \Simtabi\Laranail\Enumerator\Support\LayeredCache cache()
 * @method static \Simtabi\Laranail\Enumerator\Support\AttributesOverrideResolver overrides()
 */
class Enumerator extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return EnumeratorRegistry::class;
    }
}
