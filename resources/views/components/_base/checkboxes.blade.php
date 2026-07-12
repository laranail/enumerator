{{-- Base checkbox group partial. Pure variables. --}}
@php
    $selectedValues ??= [];
    $layout ??= 'vertical';
    $legend ??= null;
    $classes ??= 'enumerator-checkbox-group';
    $itemClasses ??= 'enumerator-checkbox-item';
    $inputClasses ??= '';
    $labelClasses ??= 'enumerator-checkbox-label';
    $legendClasses ??= 'enumerator-checkbox-legend';
    $disabled ??= false;
    $required ??= false;
    $descriptions ??= false;
    $descriptionOf ??= null;
    $rootId ??= null;
    $wireModel ??= null;
    $wireModelModifier ??= null;
    // HTML-escape both pieces in case a value contains `"` or `&`. The
    // browser decodes attribute entities before Livewire reads the value,
    // so escaping is invisible at the JS layer and fortifies the markup
    // against attribute-breakout from unexpected input.
    $wireModelAttr = $wireModel !== null
        ? 'wire:model'
            . ($wireModelModifier !== null
                ? '.' . htmlspecialchars((string) $wireModelModifier, ENT_QUOTES, 'UTF-8', false)
                : '')
            . '="' . htmlspecialchars((string) $wireModel, ENT_QUOTES, 'UTF-8', false) . '"'
        : '';
    // Arbitrary HTML attributes forwarded from the component caller
    // (data-*, aria-*, wire:model.*, x-*, etc.). Auto-built from the
    // parent's $attributes bag when not passed explicitly. For
    // Livewire array-bound checkboxes use the :wireModel prop —
    // wire:model on the wrapping <fieldset> won't populate the
    // property's array. The prop emits wire:model on every <input>.
    $extraAttrs ??= isset($attributes) && method_exists($attributes, 'except')
        ? (string) $attributes->except([
            'class', 'id', 'enum', 'name', 'selected', 'layout', 'legend',
            'framework', 'disabled', 'required', 'descriptions', 'classes',
            'item-classes', 'input-classes', 'label-classes', 'legend-classes',
            'root-id',
        ])
        : '';

    $normalized = [];
    foreach (is_iterable($selectedValues) ? $selectedValues : [$selectedValues] as $v) {
        $normalized[] = (string) $v;
    }
    $isChecked = static fn ($case): bool => in_array((string) $valueOf($case), $normalized, true);
    $fieldName = str_ends_with($name, '[]') ? $name : $name . '[]';
@endphp

<fieldset
    @if ($rootId) id="{{ $rootId }}" @endif
    class="{{ $classes }}"
    data-layout="{{ $layout }}"
    @disabled($disabled)
    {!! $extraAttrs !!}
>
    @if ($legend !== null)
        <legend class="{{ $legendClasses }}">{{ $legend }}</legend>
    @endif

    @foreach ($cases as $case)
        @php $inputId = $idFor($case); @endphp
        <div class="{{ $itemClasses }}">
            <input
                type="checkbox"
                id="{{ $inputId }}"
                name="{{ $fieldName }}"
                value="{{ $valueOf($case) }}"
                @if ($inputClasses) class="{{ $inputClasses }}" @endif
                @checked($isChecked($case))
                @disabled($disabled)
                {!! $wireModelAttr !!}
            >
            <label for="{{ $inputId }}" class="{{ $labelClasses }}">{{ $labelOf($case) }}</label>
            @if ($descriptions && $descriptionOf && ($d = $descriptionOf($case)))
                <small class="enumerator-checkbox-description">{{ $d }}</small>
            @endif
        </div>
    @endforeach
</fieldset>
