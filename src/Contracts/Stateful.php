<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Contracts;

interface Stateful extends Enumerator
{
    /**
     * Map of from-value => list of allowed target cases.
     *
     * @return array<int|string, array<int, static>>
     */
    public static function transitions(): array;

    /**
     * Cases allowed as the initial state.
     *
     * @return array<int, static>
     */
    public static function initialStates(): array;
}
