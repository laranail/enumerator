@php
    $cases ??= isset($enum) ? $enum::cases() : [];
    $selectedValues ??= $selected ?? [];
    if (! is_iterable($selectedValues)) { $selectedValues = [$selectedValues]; }
    $normalized = [];
    foreach ($selectedValues as $v) {
        if ($v instanceof \BackedEnum) { $normalized[] = (string) $v->value; }
        elseif ($v instanceof \UnitEnum) { $normalized[] = (string) $v->name; }
        else { $normalized[] = (string) $v; }
    }
    $selectedValues = $normalized;
    $valueOf ??= static fn (object $c): string|int => $c instanceof \BackedEnum ? $c->value : $c->name;
    $labelOf ??= static fn (object $c): string => method_exists($c, 'label') ? (string) $c->label() : (string) ($c->name ?? '');
    $idFor ??= static fn (object $c): string => rtrim($name, '[]') . '_' . preg_replace('/[^a-z0-9]+/i', '_', (string) $valueOf($c));
    $descriptionOf ??= static fn (object $c): ?string => method_exists($c, 'description') ? $c->description() : null;
    $classes       = $overrideClasses       ?? 'space-y-3 data-[layout=horizontal]:flex data-[layout=horizontal]:flex-wrap data-[layout=horizontal]:gap-x-6 data-[layout=horizontal]:space-y-0';
    $itemClasses   = $overrideItemClasses   ?? 'flex items-center gap-x-2';
    $inputClasses  = $overrideInputClasses  ?? 'size-4 rounded border-gray-300 text-indigo-600 focus:ring-2 focus:ring-indigo-600';
    $labelClasses  = $overrideLabelClasses  ?? 'text-sm font-medium leading-6 text-gray-900';
    $legendClasses = $overrideLegendClasses ?? 'text-sm font-semibold leading-6 text-gray-900';
    $classes = trim($classes . ' ' . ($appendClasses ?? ''));
@endphp
@include('laranail-enumerator::components._base.checkboxes', [
    'cases' => $cases, 'name' => $name, 'selectedValues' => $selectedValues,
    'layout' => $layout ?? 'vertical', 'legend' => $legend ?? null,
    'classes' => $classes, 'itemClasses' => $itemClasses, 'inputClasses' => $inputClasses,
    'labelClasses' => $labelClasses, 'legendClasses' => $legendClasses,
    'disabled' => $disabled ?? false, 'required' => $required ?? false, 'descriptions' => $descriptions ?? false,
    'valueOf' => $valueOf, 'labelOf' => $labelOf, 'idFor' => $idFor, 'descriptionOf' => $descriptionOf,
    'rootId' => $overrideRootId ?? null,
])
