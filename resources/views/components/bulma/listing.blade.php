@php
    $cases ??= isset($enum) ? $enum::cases() : [];
    $labelOf ??= static fn (object $c): string => method_exists($c, 'label') ? (string) $c->label() : (string) ($c->name ?? '');
    $valueOf ??= static fn (object $c): string|int => $c instanceof \BackedEnum ? $c->value : $c->name;
    $classes     = $overrideClasses     ?? 'panel';
    $itemClasses = $overrideItemClasses ?? 'panel-block';
    $classes = trim($classes . ' ' . ($appendClasses ?? ''));
@endphp
@include('laranail-enumerator::components._base.listing', [
    'cases' => $cases, 'classes' => $classes, 'itemClasses' => $itemClasses,
    'labelOf' => $labelOf, 'valueOf' => $valueOf,
    'rootId' => $overrideRootId ?? null,
])
