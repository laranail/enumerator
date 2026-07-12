{{-- tailwind badge variant. Self-sufficient — works anonymous OR via class component. --}}
@php
    $iconPosition ??= 'before';
    $href         ??= null;
    $ariaLabel    ??= null;
    $color = method_exists($case, 'color') ? ($case->color() ?? 'gray') : 'gray';

    // fix: anonymous-component callers pass `class="..."` via $attributes.
    // The class component populates $appendClasses; fall back to $attributes
    // when no class component is in play.
    $appendClasses ??= isset($attributes) && method_exists($attributes, 'get')
        ? trim((string) ($attributes->get('class') ?? ''))
        : '';

    $classes      = $overrideClasses      ?? sprintf('inline-flex items-center gap-x-1.5 rounded-full px-2 py-0.5 text-xs font-medium bg-%1$s-50 text-%1$s-700 ring-1 ring-inset ring-%1$s-600/20', $color);
    $iconClasses  = $overrideIconClasses  ?? 'size-3 shrink-0';
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
