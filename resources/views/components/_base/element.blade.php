{{-- Base polymorphic element partial. Pure variables. --}}
@php
    $as ??= 'span';
    $href ??= null;
    $type ??= null;
    $for ??= null;
    $showIcon ??= false;
    $showLabel ??= true;
    $showBadge ??= false;
    $iconClasses ??= 'enumerator-icon';
    $labelClasses ??= 'enumerator-label';
    $badgeClasses ??= null;
    $classes ??= 'enumerator-element';
    $rootId ??= null;
    $slot ??= '';

    $allowedTags = ['a', 'p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'span', 'div', 'button', 'label'];
    $tag = in_array($as, $allowedTags, true) ? $as : 'span';

    $caseValue = $case instanceof \BackedEnum
        ? (string) $case->value
        : (method_exists($case, 'getValue') ? (string) $case->getValue() : $case->name);
    $caseLabel = method_exists($case, 'label') ? (string) $case->label() : (string) $case->name;
    $caseIcon  = method_exists($case, 'icon')  ? $case->icon() : null;
    $resolvedHref = $href !== null
        ? strtr($href, ['{value}' => $caseValue, '{name}' => $case->name])
        : null;
@endphp

<{{ $tag }}
    @if ($rootId) id="{{ $rootId }}" @endif
    class="{{ $classes }}"
    @if ($tag === 'a' && $resolvedHref !== null) href="{{ $resolvedHref }}" @endif
    @if ($tag === 'button') type="{{ $type ?? 'button' }}" @endif
    @if ($tag === 'label' && $for !== null) for="{{ $for }}" @endif
    data-value="{{ $caseValue }}"
    data-name="{{ $case->name }}"
>
@if ($showBadge)
<x-laranail-enumerator::badge :case="$case" :classes="$badgeClasses" />
@else
@if ($showIcon && $caseIcon)<span class="{{ $iconClasses }}" aria-hidden="true">{!! $caseIcon !!}</span>@endif
@if ($showLabel)<span class="{{ $labelClasses }}">{{ $caseLabel }}</span>@endif
@endif
{!! $slot !!}
</{{ $tag }}>
