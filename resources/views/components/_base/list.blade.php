{{-- Base flat-list partial. Pure variables. --}}
@php
    $classes ??= 'enumerator-list';
    $itemClasses ??= 'enumerator-list-item';
    $valueOf ??= null;
    $rootId ??= null;
@endphp

<ul
    @if ($rootId) id="{{ $rootId }}" @endif
    class="{{ $classes }}"
    role="list"
>
    @foreach ($cases as $case)
        <li class="{{ $itemClasses }}" @if ($valueOf) data-value="{{ $valueOf($case) }}" @endif>{{ $labelOf($case) }}</li>
    @endforeach
</ul>
