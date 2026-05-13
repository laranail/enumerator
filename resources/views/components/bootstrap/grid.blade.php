@php
    $cases ??= isset($enum) ? $enum::cases() : [];
    $labelOf ??= static fn (object $c): string => method_exists($c, 'label') ? (string) $c->label() : (string) ($c->name ?? '');
    $descriptionOf ??= static fn (object $c): ?string => method_exists($c, 'description') ? $c->description() : null;
    $valueOf ??= static fn (object $c): string|int => $c instanceof \BackedEnum ? $c->value : $c->name;
    $classes            = $overrideClasses            ?? 'row g-3 row-cols-1 row-cols-md-2 row-cols-lg-3';
    $itemClasses        = $overrideItemClasses        ?? 'col card h-100 p-3 border-0 shadow-sm';
    $labelClasses       = $overrideLabelClasses       ?? 'card-title h6 mb-1';
    $descriptionClasses = $overrideDescriptionClasses ?? 'card-text small text-body-secondary';
    $classes = trim($classes . ' ' . ($appendClasses ?? ''));
@endphp
@include('laranail-enumerator::components._base.grid', [
    'cases' => $cases, 'columns' => $columns ?? 3, 'showBadges' => $showBadges ?? false,
    'classes' => $classes, 'itemClasses' => $itemClasses, 'labelClasses' => $labelClasses,
    'descriptionClasses' => $descriptionClasses,
    'labelOf' => $labelOf, 'descriptionOf' => $descriptionOf, 'valueOf' => $valueOf,
    'rootId' => $overrideRootId ?? null,
])
