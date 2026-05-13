@php
    $cases ??= isset($enum) ? $enum::cases() : [];
    $labelOf ??= static fn (object $c): string => method_exists($c, 'label') ? (string) $c->label() : (string) ($c->name ?? '');
    $descriptionOf ??= static fn (object $c): ?string => method_exists($c, 'description') ? $c->description() : null;
    $valueOf ??= static fn (object $c): string|int => $c instanceof \BackedEnum ? $c->value : $c->name;
    $classes            = $overrideClasses            ?? 'enumerator-grid';
    $itemClasses        = $overrideItemClasses        ?? 'enumerator-grid-item';
    $labelClasses       = $overrideLabelClasses       ?? 'enumerator-grid-label';
    $descriptionClasses = $overrideDescriptionClasses ?? 'enumerator-grid-description';
    $classes = trim($classes . ' ' . ($appendClasses ?? ''));
@endphp
@include('laranail-enumerator::components._base.grid', [
    'cases' => $cases, 'columns' => $columns ?? 3, 'showBadges' => $showBadges ?? false,
    'classes' => $classes, 'itemClasses' => $itemClasses, 'labelClasses' => $labelClasses,
    'descriptionClasses' => $descriptionClasses,
    'labelOf' => $labelOf, 'descriptionOf' => $descriptionOf, 'valueOf' => $valueOf,
    'rootId' => $overrideRootId ?? null,
])
