<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Integrations\Filament\Forms;

use Filament\Forms\Components\Radio;
use Simtabi\Laranail\Enumerator\Support\OptionsArrayBuilder;

if (! class_exists(Radio::class)) {
    return;
}

final class EnumeratorRadio extends Radio
{
    /**
     * @param  class-string  $enumClass
     */
    public function enumerator(string $enumClass): static
    {
        $this->options(static fn (): array => OptionsArrayBuilder::for($enumClass));

        return $this;
    }
}
