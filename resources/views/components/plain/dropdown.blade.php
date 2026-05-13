@php
    $cases ??= isset($enum) ? $enum::cases() : [];
    $valueOf ??= static fn (object $c): string|int => $c instanceof \BackedEnum ? $c->value : $c->name;
    $labelOf ??= static fn (object $c): string => method_exists($c, 'label') ? (string) $c->label() : (string) ($c->name ?? '');
    $groups ??= \Simtabi\Laranail\Enumerator\Support\BladeViewHelpers::buildGroups($cases, $groupsBy ?? null, $groupLabels ?? []);
    $wrapperClasses     = $overrideWrapperClasses     ?? 'enumerator-dropdown';
    $labelClasses       = $overrideLabelClasses       ?? 'enumerator-dropdown-label';
    $descriptionClasses = $overrideDescriptionClasses ?? 'enumerator-dropdown-description';
    $classes            = $overrideClasses            ?? 'enumerator-select';
    $optionClasses      = $overrideOptionClasses      ?? '';
    $classes = trim($classes . ' ' . ($appendClasses ?? ''));
@endphp
@include('laranail-enumerator::components._base.dropdown', [
    'cases' => $cases, 'name' => $name, 'selectedValue' => $selectedValue ?? null,
    'nullable' => $nullable ?? false, 'placeholder' => $placeholder ?? 'Select an option…',
    'multiple' => $multiple ?? false, 'size' => $size ?? null,
    'disabled' => $disabled ?? false, 'required' => $required ?? false,
    'classes' => $classes, 'optionClasses' => $optionClasses,
    'wrapperClasses' => $wrapperClasses, 'labelClasses' => $labelClasses, 'descriptionClasses' => $descriptionClasses,
    'groups' => $groups ?? null, 'valueOf' => $valueOf, 'labelOf' => $labelOf,
    'labelText' => $labelText ?? null, 'description' => $description ?? null,
    'ariaLabel' => $ariaLabel ?? null, 'searchable' => $searchable ?? false, 'clearable' => $clearable ?? false,
    'rootId' => $overrideRootId ?? null, 'wrapperId' => $overrideWrapperId ?? null, 'labelId' => $overrideLabelId ?? null,
])
