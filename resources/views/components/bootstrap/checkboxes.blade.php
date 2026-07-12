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
    $classes       = $overrideClasses       ?? 'd-flex flex-column gap-2';
    $itemClasses   = $overrideItemClasses   ?? 'form-check';
    $inputClasses  = $overrideInputClasses  ?? 'form-check-input';
    $labelClasses  = $overrideLabelClasses  ?? 'form-check-label';
    $legendClasses = $overrideLegendClasses ?? 'form-label fw-semibold';
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
