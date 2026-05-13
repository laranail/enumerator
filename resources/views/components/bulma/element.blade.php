@php
    $classes      = $overrideClasses      ?? 'is-inline-flex is-align-items-center';
    $iconClasses  = $overrideIconClasses  ?? null;
    $labelClasses = $overrideLabelClasses ?? null;
    $badgeClasses = $overrideBadgeClasses ?? null;
    $classes = trim($classes . ' ' . ($appendClasses ?? ''));
@endphp
@include('laranail-enumerator::components._base.element', [
    'case' => $case, 'as' => $as ?? 'span', 'href' => $href ?? null, 'type' => $type ?? null, 'for' => $for ?? null,
    'showIcon' => $showIcon ?? false, 'showLabel' => $showLabel ?? true, 'showBadge' => $showBadge ?? false,
    'iconClasses' => $iconClasses, 'labelClasses' => $labelClasses, 'badgeClasses' => $badgeClasses, 'classes' => $classes,
    'rootId' => $overrideRootId ?? null,
    'slot' => $slot ?? '',
])
