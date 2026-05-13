@php
    $cases ??= isset($enum) ? $enum::cases() : [];
    $valueOf ??= static fn (object $c): string|int => $c instanceof \BackedEnum ? $c->value : $c->name;
    $labelOf ??= static fn (object $c): string => method_exists($c, 'label') ? (string) $c->label() : (string) ($c->name ?? '');
    $groups ??= \Simtabi\Laranail\Enumerator\Support\BladeViewHelpers::buildGroups($cases, $groupsBy ?? null, $groupLabels ?? []);

    // fix: anonymous-component callers pass attributes via $attributes.
    $appendClasses ??= isset($attributes) && method_exists($attributes, 'get')
        ? trim((string) ($attributes->get('class') ?? ''))
        : '';
    $rootIdResolved = $overrideRootId
        ?? (isset($attributes) && method_exists($attributes, 'get') ? $attributes->get('id') : null);
    // Forward arbitrary html attributes (data-*, aria-*, etc.) into the
    // rendered <select>. Known props are already consumed; anything else
    // in $attributes flows through.
    $extraAttrs = '';
    if (isset($attributes) && method_exists($attributes, 'except')) {
        $forwarded = $attributes->except(['class', 'id', 'enum', 'name', 'selected', 'nullable', 'placeholder', 'multiple', 'size', 'disabled', 'required', 'framework', 'groups-by', 'groups', 'group-labels', 'aria-label', 'classes', 'option-classes', 'root-id']);
        $extraAttrs = (string) $forwarded;
    }

    $classes       = $overrideClasses       ?? 'form-select';
    $optionClasses = $overrideOptionClasses ?? '';
    $classes = trim($classes . ' ' . $appendClasses);
@endphp
@include('laranail-enumerator::components._base.select', [
    'cases' => $cases, 'name' => $name, 'selectedValue' => $selectedValue ?? null,
    'nullable' => $nullable ?? false, 'placeholder' => $placeholder ?? 'Select an option…',
    'multiple' => $multiple ?? false, 'size' => $size ?? null,
    'disabled' => $disabled ?? false, 'required' => $required ?? false,
    'classes' => $classes, 'optionClasses' => $optionClasses, 'groups' => $groups ?? null,
    'valueOf' => $valueOf, 'labelOf' => $labelOf, 'ariaLabel' => $ariaLabel ?? null,
    'rootId' => $rootIdResolved,
    'extraAttrs' => $extraAttrs,
])
