@php
    $cases ??= isset($enum) ? $enum::cases() : [];
    $valueOf ??= static fn (object $c): string|int => $c instanceof \BackedEnum ? $c->value : $c->name;
    $labelOf ??= static fn (object $c): string => method_exists($c, 'label') ? (string) $c->label() : (string) ($c->name ?? '');
    $idFor ??= static fn (object $c): string => $name . '_' . preg_replace('/[^a-z0-9]+/i', '_', (string) $valueOf($c));
    $descriptionOf ??= static fn (object $c): ?string => method_exists($c, 'description') ? $c->description() : null;
    $selectedValue = $selectedValue ?? ($selected ?? null);
    if ($selectedValue instanceof \BackedEnum) { $selectedValue = $selectedValue->value; }
    elseif ($selectedValue instanceof \UnitEnum) { $selectedValue = $selectedValue->name; }
    $classes       = $overrideClasses       ?? 'd-flex flex-column gap-2';
    $itemClasses   = $overrideItemClasses   ?? 'form-check';
    $inputClasses  = $overrideInputClasses  ?? 'form-check-input';
    $labelClasses  = $overrideLabelClasses  ?? 'form-check-label';
    $legendClasses = $overrideLegendClasses ?? 'form-label fw-semibold';
    $classes = trim($classes . ' ' . ($appendClasses ?? ''));
@endphp
@include('laranail-enumerator::components._base.radio', [
    'cases' => $cases, 'name' => $name, 'selectedValue' => $selectedValue,
    'layout' => $layout ?? 'vertical', 'legend' => $legend ?? null,
    'classes' => $classes, 'itemClasses' => $itemClasses, 'inputClasses' => $inputClasses,
    'labelClasses' => $labelClasses, 'legendClasses' => $legendClasses,
    'disabled' => $disabled ?? false, 'required' => $required ?? false, 'descriptions' => $descriptions ?? false,
    'valueOf' => $valueOf, 'labelOf' => $labelOf, 'idFor' => $idFor, 'descriptionOf' => $descriptionOf,
    'rootId' => $overrideRootId ?? null,
])
