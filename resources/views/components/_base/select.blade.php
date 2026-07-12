{{-- Base select partial. Pure variables — no $attributes magic. --}}
@php
    $selectedValue ??= null;
    $nullable ??= false;
    $placeholder ??= 'Select an option…';
    $multiple ??= false;
    $size ??= null;
    $disabled ??= false;
    $required ??= false;
    $classes ??= 'enumerator-select';
    $optionClasses ??= null;
    $groups ??= null;
    $ariaLabel ??= null;
    $rootId ??= null;
    // Arbitrary HTML attributes forwarded from the component caller
    // (data-*, aria-*, wire:model.*, x-*, etc.) flow through this
    // string. Framework views may pass `extraAttrs` explicitly; if
    // they don't, we auto-build from the parent's $attributes bag.
    // Laravel's ComponentAttributeBag::__toString() HTML-escapes
    // attribute values, so the {!! !!} output below is safe by
    // construction (see tests/Unit/Blade/SelectComponentContractTest).
    $extraAttrs ??= isset($attributes) && method_exists($attributes, 'except')
        ? (string) $attributes->except([
            'class', 'id', 'enum', 'name', 'selected', 'nullable', 'placeholder',
            'multiple', 'size', 'disabled', 'required', 'framework',
            'groups-by', 'groups', 'group-labels', 'aria-label', 'classes',
            'option-classes', 'root-id',
        ])
        : '';

    $inputId = $rootId ?? $name;
    $renderName = $multiple ? rtrim($name, '[]') . '[]' : $name;
    $isSelected = static function ($case) use ($selectedValue, $valueOf, $multiple): bool {
        if ($selectedValue === null) {
            return false;
        }
        $needle = (string) $valueOf($case);
        if ($multiple && is_iterable($selectedValue)) {
            foreach ($selectedValue as $v) {
                if ((string) $v === $needle) {
                    return true;
                }
            }
            return false;
        }
        return (string) $selectedValue === $needle;
    };
@endphp

<select
    name="{{ $renderName }}"
    id="{{ $inputId }}"
    class="{{ $classes }}"
    @if ($multiple) multiple @endif
    @if ($size) size="{{ $size }}" @endif
    @disabled($disabled)
    @required($required)
    @if ($ariaLabel) aria-label="{{ $ariaLabel }}" @endif
    {!! $extraAttrs !!}
>
    @if ($nullable && ! $multiple)
        <option value="">{{ $placeholder }}</option>
    @endif

    @if ($groups !== null)
        @foreach ($groups as $groupLabel => $groupCases)
            <optgroup label="{{ $groupLabel === '' ? '—' : $groupLabel }}">
                @foreach ($groupCases as $case)
                    <option value="{{ $valueOf($case) }}" @selected($isSelected($case)) @if ($optionClasses) class="{{ $optionClasses }}" @endif>{{ $labelOf($case) }}</option>
                @endforeach
            </optgroup>
        @endforeach
    @else
        @foreach ($cases as $case)
            <option value="{{ $valueOf($case) }}" @selected($isSelected($case)) @if ($optionClasses) class="{{ $optionClasses }}" @endif>{{ $labelOf($case) }}</option>
        @endforeach
    @endif
</select>
