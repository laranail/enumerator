{{-- Base radio group partial. Pure variables. --}}
@php
    $selectedValue ??= null;
    $layout ??= 'vertical';
    $legend ??= null;
    $classes ??= 'enumerator-radio-group';
    $itemClasses ??= 'enumerator-radio-item';
    $inputClasses ??= '';
    $labelClasses ??= 'enumerator-radio-label';
    $legendClasses ??= 'enumerator-radio-legend';
    $disabled ??= false;
    $required ??= false;
    $descriptions ??= false;
    $descriptionOf ??= null;
    $rootId ??= null;
    $wireModel ??= null;
    $wireModelModifier ??= null;
    $wireModelAttr = $wireModel !== null
        ? 'wire:model' . ($wireModelModifier !== null ? '.' . $wireModelModifier : '') . '="' . $wireModel . '"'
        : '';
    // Arbitrary HTML attributes forwarded from the component caller
    // (data-*, aria-*, wire:model.*, x-*, etc.). Auto-built from the
    // parent's $attributes bag when not passed explicitly. For
    // explicit input-level Livewire binding (preferred for radio
    // groups) use the :wireModel prop; that emits wire:model on each
    // <input>.
    $extraAttrs ??= isset($attributes) && method_exists($attributes, 'except')
        ? (string) $attributes->except([
            'class', 'id', 'enum', 'name', 'selected', 'layout', 'legend',
            'framework', 'disabled', 'required', 'descriptions', 'classes',
            'item-classes', 'input-classes', 'label-classes', 'legend-classes',
            'root-id',
        ])
        : '';

    $isSelected = static fn ($case): bool => $selectedValue !== null
        && (string) $selectedValue === (string) $valueOf($case);
@endphp

<fieldset
    @if ($rootId) id="{{ $rootId }}" @endif
    class="{{ $classes }}"
    role="radiogroup"
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
                type="radio"
                id="{{ $inputId }}"
                name="{{ $name }}"
                value="{{ $valueOf($case) }}"
                @if ($inputClasses) class="{{ $inputClasses }}" @endif
                @checked($isSelected($case))
                @disabled($disabled)
                @required($required)
                {!! $wireModelAttr !!}
            >
            <label for="{{ $inputId }}" class="{{ $labelClasses }}">{{ $labelOf($case) }}</label>
            @if ($descriptions && $descriptionOf && ($d = $descriptionOf($case)))
                <small class="enumerator-radio-description">{{ $d }}</small>
            @endif
        </div>
    @endforeach
</fieldset>
