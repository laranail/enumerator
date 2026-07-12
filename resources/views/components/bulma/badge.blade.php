{{-- bulma badge variant. Self-sufficient — works anonymous OR via class component. --}}
@php
    $iconPosition ??= 'before';
    $href         ??= null;
    $ariaLabel    ??= null;
    $color = method_exists($case, 'color') ? ($case->color() ?? 'info') : 'info';
    $color = ['error' => 'danger', 'destructive' => 'danger'][$color] ?? $color;

    // fix: anonymous-component callers pass `class="..."` via $attributes.
    $appendClasses ??= isset($attributes) && method_exists($attributes, 'get')
        ? trim((string) ($attributes->get('class') ?? ''))
        : '';

    $classes      = $overrideClasses      ?? 'tag is-' . $color . ' is-rounded';
    $iconClasses  = $overrideIconClasses  ?? 'icon is-small mr-1';
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
