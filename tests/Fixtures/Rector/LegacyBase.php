<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Tests\Fixtures\Rector;

/**
 * Stands in for whatever class-constant enum base an application invented.
 *
 * The rule is configured with a base class name and does not load it, so this
 * only needs to exist for the fixture to be readable — not for the codemod.
 */
abstract class LegacyBase
{
    public string $value = '';
}
