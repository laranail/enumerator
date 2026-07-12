<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Integrations\Filament\Forms;

use Filament\Forms\Components\Select;
use Simtabi\Laranail\Enumerator\Support\OptionsArrayBuilder;

if (! class_exists(Select::class)) {
    return;
}

final class EnumeratorSelect extends Select
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
