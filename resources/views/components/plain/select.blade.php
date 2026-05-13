@php
    $cases ??= isset($enum) ? $enum::cases() : [];
    $valueOf ??= static fn (object $c): string|int => $c instanceof \BackedEnum ? $c->value : $c->name;
    $labelOf ??= static fn (object $c): string => method_exists($c, 'label') ? (string) $c->label() : (string) ($c->name ?? '');
    $groups ??= \Simtabi\Laranail\Enumerator\Support\BladeViewHelpers::buildGroups($cases, $groupsBy ?? null, $groupLabels ?? []);
    $classes       = $overrideClasses       ?? 'enumerator-select';
    $optionClasses = $overrideOptionClasses ?? '';
    $classes = trim($classes . ' ' . ($appendClasses ?? ''));
@endphp
@include('laranail-enumerator::components._base.select', [
    'cases' => $cases, 'name' => $name, 'selectedValue' => $selectedValue ?? null,
    'nullable' => $nullable ?? false, 'placeholder' => $placeholder ?? 'Select an option…',
    'multiple' => $multiple ?? false, 'size' => $size ?? null,
    'disabled' => $disabled ?? false, 'required' => $required ?? false,
    'classes' => $classes, 'optionClasses' => $optionClasses, 'groups' => $groups ?? null,
    'valueOf' => $valueOf, 'labelOf' => $labelOf, 'ariaLabel' => $ariaLabel ?? null,
    'rootId' => $overrideRootId ?? null,
])
