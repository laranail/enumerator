<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Integrations\Filament\Filters;

use Filament\Tables\Filters\SelectFilter;
use Simtabi\Laranail\Enumerator\Support\OptionsArrayBuilder;

if (! class_exists(SelectFilter::class)) {
    return;
}

final class EnumeratorFilter extends SelectFilter
{
    /**
     * @param  class-string  $enumClass
     */
    public static function for(string $enumClass, string $column): self
    {
        /** @var self $filter */
        $filter = self::make($column);
        $filter->options(static fn (): array => OptionsArrayBuilder::for($enumClass));

        return $filter;
    }
}
