{{--
    Base dropdown partial — wrapper <label>/<small>/<select> trio with
    JS-friendly data-* hooks (data-searchable, data-clearable) designed for
    Alpine, Tom Select, Choices.js, Select2, etc. Inlines the select markup
    so the props flow through a single render — no nested anonymous component.
--}}
@php
    $selectedValue ??= null;
    $nullable ??= false;
    $placeholder ??= 'Select an option…';
    $multiple ??= false;
    $size ??= null;
    $disabled ??= false;
    $required ??= false;
    $classes ??= null;
    $optionClasses ??= null;
    $wrapperClasses ??= null;
    $labelClasses ??= null;
    $descriptionClasses ??= null;
    $groups ??= null;
    $labelText ??= null;
    $description ??= null;
    $ariaLabel ??= null;
    $searchable ??= false;
    $clearable ??= false;

    $inputId = $attributes->get('id', $name);
    $describedById = $description !== null ? $inputId . '-description' : null;
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

<div {{ $attributes->only(['id'])->class(['enumerator-dropdown', $wrapperClasses]) }}>
    @if ($labelText !== null)
        <label for="{{ $inputId }}" class="{{ $labelClasses ?? 'enumerator-dropdown-label' }}">
            {{ $labelText }}
            @if ($required)<span aria-hidden="true">*</span>@endif
        </label>
    @endif

    @if ($description !== null)
        <small id="{{ $describedById }}" class="{{ $descriptionClasses ?? 'enumerator-dropdown-description' }}">
            {{ $description }}
        </small>
    @endif

    <select
        name="{{ $renderName }}"
        id="{{ $inputId }}"
        @if ($multiple) multiple @endif
        @if ($size) size="{{ $size }}" @endif
        @disabled($disabled)
        @required($required)
        @if ($ariaLabel ?? $labelText) aria-label="{{ $ariaLabel ?? $labelText }}" @endif
        @if ($describedById) aria-describedby="{{ $describedById }}" @endif
        data-searchable="{{ $searchable ? 'true' : 'false' }}"
        data-clearable="{{ $clearable ? 'true' : 'false' }}"
        class="enumerator-select {{ $classes }}"
    >
        @if ($nullable && ! $multiple)
            <option value="">{{ $placeholder }}</option>
        @endif

        @if ($groups !== null)
            @foreach ($groups as $groupLabel => $groupCases)
                <optgroup label="{{ $groupLabel === '' ? '—' : $groupLabel }}">
                    @foreach ($groupCases as $case)
                        <option value="{{ $valueOf($case) }}" @selected($isSelected($case))>{{ $labelOf($case) }}</option>
                    @endforeach
                </optgroup>
            @endforeach
        @else
            @foreach ($cases as $case)
                <option value="{{ $valueOf($case) }}" @selected($isSelected($case))>{{ $labelOf($case) }}</option>
            @endforeach
        @endif
    </select>
</div>
