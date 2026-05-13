<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Contracts;

interface TransitionHook
{
    /**
     * Called before a transition. Return false to abort.
     *
     * @param  object  $from  the source case (Enumerator or AbstractEnumeratorClass)
     * @param  object  $to  the target case
     */
    public function before(object $from, object $to): bool;

    /**
     * Called after a successful transition.
     */
    public function after(object $from, object $to): void;
}
