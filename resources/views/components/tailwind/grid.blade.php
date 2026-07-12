@php
    $cases ??= isset($enum) ? $enum::cases() : [];
    $labelOf ??= static fn (object $c): string => method_exists($c, 'label') ? (string) $c->label() : (string) ($c->name ?? '');
    $descriptionOf ??= static fn (object $c): ?string => method_exists($c, 'description') ? $c->description() : null;
    $valueOf ??= static fn (object $c): string|int => $c instanceof \BackedEnum ? $c->value : $c->name;
    $classes            = $overrideClasses            ?? 'grid gap-4 data-[columns="1"]:grid-cols-1 data-[columns="2"]:grid-cols-2 data-[columns="3"]:grid-cols-3 data-[columns="4"]:grid-cols-4 data-[columns="5"]:grid-cols-5 data-[columns="6"]:grid-cols-6';
    $itemClasses        = $overrideItemClasses        ?? 'rounded-lg bg-white p-4 ring-1 ring-gray-200 shadow-sm';
    $labelClasses       = $overrideLabelClasses       ?? 'text-sm font-semibold text-gray-900';
    $descriptionClasses = $overrideDescriptionClasses ?? 'mt-1 text-sm text-gray-600';
    $classes = trim($classes . ' ' . ($appendClasses ?? ''));
@endphp
@include('laranail-enumerator::components._base.grid', [
    'cases' => $cases, 'columns' => $columns ?? 3, 'showBadges' => $showBadges ?? false,
    'classes' => $classes, 'itemClasses' => $itemClasses, 'labelClasses' => $labelClasses,
    'descriptionClasses' => $descriptionClasses,
    'labelOf' => $labelOf, 'descriptionOf' => $descriptionOf, 'valueOf' => $valueOf,
    'rootId' => $overrideRootId ?? null,
])
