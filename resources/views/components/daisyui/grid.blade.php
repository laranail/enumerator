@php
    $cases ??= isset($enum) ? $enum::cases() : [];
    $labelOf ??= static fn (object $c): string => method_exists($c, 'label') ? (string) $c->label() : (string) ($c->name ?? '');
    $descriptionOf ??= static fn (object $c): ?string => method_exists($c, 'description') ? $c->description() : null;
    $valueOf ??= static fn (object $c): string|int => $c instanceof \BackedEnum ? $c->value : $c->name;
    $classes            = $overrideClasses            ?? 'grid gap-4';
    $itemClasses        = $overrideItemClasses        ?? 'card card-bordered bg-base-100 shadow-sm card-md';
    $labelClasses       = $overrideLabelClasses       ?? 'card-title text-base';
    $descriptionClasses = $overrideDescriptionClasses ?? 'text-sm text-base-content/70';
    $classes = trim($classes . ' ' . ($appendClasses ?? ''));
@endphp
@include('laranail-enumerator::components._base.grid', [
    'cases' => $cases, 'columns' => $columns ?? 3, 'showBadges' => $showBadges ?? false,
    'classes' => $classes, 'itemClasses' => $itemClasses, 'labelClasses' => $labelClasses,
    'descriptionClasses' => $descriptionClasses,
    'labelOf' => $labelOf, 'descriptionOf' => $descriptionOf, 'valueOf' => $valueOf,
    'rootId' => $overrideRootId ?? null,
])
