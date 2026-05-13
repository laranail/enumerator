<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Contracts;

/**
 * Marker interface implemented by any enum (native or class-const) integrated
 * with this package.
 *
 * Native PHP enums implementing this contract automatically expose UnitEnum
 * / BackedEnum semantics in addition to the methods provided by
 * Concerns\HasEnumeratorBehavior.
 */
interface Enumerator {}
