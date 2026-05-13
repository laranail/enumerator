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
    // arbitrary HTML attributes forwarded from anonymous-component
    // callers (data-*, aria-*, etc.) flow through this string.
    $extraAttrs ??= '';

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
