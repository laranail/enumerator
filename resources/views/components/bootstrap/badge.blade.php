{{-- bootstrap badge variant. Self-sufficient — works anonymous OR via class component. --}}
@php
    $iconPosition ??= 'before';
    $href         ??= null;
    $ariaLabel    ??= null;
    $color = method_exists($case, 'color') ? ($case->color() ?? 'secondary') : 'secondary';

    // fix: anonymous-component callers pass `class="..."` via $attributes.
    $appendClasses ??= isset($attributes) && method_exists($attributes, 'get')
        ? trim((string) ($attributes->get('class') ?? ''))
        : '';

    $classes      = $overrideClasses      ?? 'badge text-bg-' . $color . ' rounded-pill';
    $iconClasses  = $overrideIconClasses  ?? 'me-1 align-middle';
    $labelClasses = $overrideLabelClasses ?? '';
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
