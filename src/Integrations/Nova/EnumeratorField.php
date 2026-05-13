<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Integrations\Nova;

use Laravel\Nova\Fields\Select;
use Simtabi\Laranail\Enumerator\Support\OptionsArrayBuilder;

if (! class_exists(Select::class)) {
    return;
}

final class EnumeratorField extends Select
{
    /**
     * @param  class-string  $enumClass
     */
    public function forEnumerator(string $enumClass): static
    {
        $this->options(static fn (): array => OptionsArrayBuilder::for($enumClass));

        return $this;
    }
}
