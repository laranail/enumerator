{{-- plain badge variant. Self-sufficient — works anonymous OR via class component. --}}
@php
    $iconPosition ??= 'before';
    $href         ??= null;
    $ariaLabel    ??= null;
    $color = method_exists($case, 'color') ? ($case->color() ?? 'default') : 'default';

    // fix: anonymous-component callers pass `class="..."` via $attributes.
    $appendClasses ??= isset($attributes) && method_exists($attributes, 'get')
        ? trim((string) ($attributes->get('class') ?? ''))
        : '';

    $classes      = $overrideClasses      ?? 'enumerator-badge enumerator-badge-' . $color;
    $iconClasses  = $overrideIconClasses  ?? 'enumerator-icon';
    $labelClasses = $overrideLabelClasses ?? 'enumerator-label';
    $classes = trim($classes . ' ' . $appendClasses);
@endphp
@include('laranail-enumerator::components._base.badge', [
    'case'         => $case,
    'classes'      => $classes,
    'iconClasses'  => $iconClasses,
    'labelClasses' => $labelClasses,
    'iconPosition' => $iconPosition,
    'href'         => $href,
    'ariaLabel'    => $ariaLabel,
    'rootId'       => $overrideRootId ?? null,
])
