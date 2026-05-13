{{-- Base grid partial. Pure variables. --}}
@php
    $columns ??= 3;
    $showBadges ??= false;
    $classes ??= 'enumerator-grid';
    $itemClasses ??= 'enumerator-grid-item';
    $labelClasses ??= 'enumerator-grid-label';
    $descriptionClasses ??= 'enumerator-grid-description';
    $descriptionOf ??= null;
    $valueOf ??= null;
    $rootId ??= null;
@endphp

<ul
    @if ($rootId) id="{{ $rootId }}" @endif
    class="{{ $classes }}"
    role="list"
    data-columns="{{ $columns }}"
>
    @foreach ($cases as $case)
        <li class="{{ $itemClasses }}" @if ($valueOf) data-value="{{ $valueOf($case) }}" @endif>
            @if ($showBadges)
                <x-laranail-enumerator::badge :case="$case" />
            @else
                <h3 class="{{ $labelClasses }}">{{ $labelOf($case) }}</h3>
            @endif

            @if ($descriptionOf && ($d = $descriptionOf($case)))
                <p class="{{ $descriptionClasses }}">{{ $d }}</p>
            @endif
        </li>
    @endforeach
</ul>
