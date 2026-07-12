<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Integrations\Nova;

use Laravel\Nova\Filters\Filter;
use Laravel\Nova\Http\Requests\NovaRequest;
use Simtabi\Laranail\Enumerator\Support\IsEnumeratorClass;

if (! class_exists(Filter::class)) {
    return;
}

abstract class EnumeratorFilter extends Filter
{
    /** @var class-string */
    public string $enumClass;

    public function options(NovaRequest $request): array
    {
        // support both native enums and AbstractEnumeratorClass subclasses.
        if (! IsEnumeratorClass::check($this->enumClass)) {
            return [];
        }
        // Nova convention: [label => value] (inverse of Filament's [value => label]).
        $options = [];
        foreach (IsEnumeratorClass::casesOf($this->enumClass) as $case) {
            $key = method_exists($case, 'label')
                ? (string) $case->label()
                : (string) (method_exists($case, 'getKey') ? $case->getKey() : ($case->name ?? IsEnumeratorClass::valueOf($case)));
            $options[$key] = IsEnumeratorClass::valueOf($case);
        }

        return $options;
    }
}
