{{-- Base badge partial. Pure variables — no $attributes magic, safe to @include. --}}
@php
    $iconPosition ??= 'before';
    $href ??= null;
    $ariaLabel ??= null;
    $classes ??= 'enumerator-badge';
    $iconClasses ??= 'enumerator-icon';
    $labelClasses ??= 'enumerator-label';
    $rootId ??= null;

    $caseLabel = method_exists($case, 'label') ? (string) $case->label() : (string) $case->name;
    $caseIcon  = method_exists($case, 'icon')  ? $case->icon()           : null;
    $caseColor = method_exists($case, 'color') ? $case->color()          : null;
    $caseValue = $case instanceof \BackedEnum
        ? (string) $case->value
        : (method_exists($case, 'getValue') ? (string) $case->getValue() : $case->name);
    $resolvedHref = $href !== null
        ? strtr($href, ['{value}' => $caseValue, '{name}' => $case->name])
        : null;
    $tag = $resolvedHref !== null ? 'a' : 'span';
    $accessibleLabel = $ariaLabel ?? $caseLabel;
@endphp

<{{ $tag }}
    @if ($rootId) id="{{ $rootId }}" @endif
    class="{{ $classes }}"
    @if ($resolvedHref !== null) href="{{ $resolvedHref }}" @endif
    role="status"
    aria-label="{{ $accessibleLabel }}"
    @if ($caseColor) data-color="{{ $caseColor }}" @endif
    data-value="{{ $caseValue }}"
    data-name="{{ $case->name }}"
>@if ($caseIcon && $iconPosition === 'before')<span class="{{ $iconClasses }}" aria-hidden="true">{!! $caseIcon !!}</span>@endif<span class="{{ $labelClasses }}">{{ $caseLabel }}</span>@if ($caseIcon && $iconPosition === 'after')<span class="{{ $iconClasses }}" aria-hidden="true">{!! $caseIcon !!}</span>@endif</{{ $tag }}>
