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

    $isSelected = static fn ($case): bool => $selectedValue !== null
        && (string) $selectedValue === (string) $valueOf($case);
@endphp

<fieldset
    @if ($rootId) id="{{ $rootId }}" @endif
    class="{{ $classes }}"
    role="radiogroup"
    data-layout="{{ $layout }}"
    @disabled($disabled)
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
            >
            <label for="{{ $inputId }}" class="{{ $labelClasses }}">{{ $labelOf($case) }}</label>
            @if ($descriptions && $descriptionOf && ($d = $descriptionOf($case)))
                <small class="enumerator-radio-description">{{ $d }}</small>
            @endif
        </div>
    @endforeach
</fieldset>
