@php
    $cases ??= isset($enum) ? $enum::cases() : [];
    $valueOf ??= static fn (object $c): string|int => $c instanceof \BackedEnum ? $c->value : $c->name;
    $labelOf ??= static fn (object $c): string => method_exists($c, 'label') ? (string) $c->label() : (string) ($c->name ?? '');
    $groups ??= \Simtabi\Laranail\Enumerator\Support\BladeViewHelpers::buildGroups($cases, $groupsBy ?? null, $groupLabels ?? []);
    $classes       = $overrideClasses       ?? 'block w-full rounded-md border-0 py-1.5 pl-3 pr-10 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6';
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
