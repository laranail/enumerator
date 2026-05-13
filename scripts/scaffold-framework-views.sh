#!/usr/bin/env bash
# scripts/scaffold-framework-views.sh
#
# Writes 40 framework Blade variants — 8 components × 5 frameworks. Each
# variant is self-sufficient: it computes any missing data (e.g. $cases from
# $enum) so it works whether invoked as an anonymous component
# (`<x-laranail-enumerator::tailwind.badge>`) or routed through the canonical
# class component (`<x-laranail-enumerator::badge framework="tailwind">`).
#
# CSS class strings live INLINE in each variant — no config indirection.
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"
NS=laranail-enumerator

view_path() { mkdir -p "resources/views/components/${1}"; echo "resources/views/components/${1}/${2}.blade.php"; }

# ---------------- BADGE ----------------
write_badge() {
    local fw="$1" classExpr="$2" iconCls="$3" labelCls="$4" defaultColor="$5" colorMap="$6"
    cat > "$(view_path "$fw" badge)" <<EOF
{{-- ${fw} badge variant. Self-sufficient — works anonymous OR via class component. --}}
@php
    \$iconPosition ??= 'before';
    \$href         ??= null;
    \$ariaLabel    ??= null;
    \$color = method_exists(\$case, 'color') ? (\$case->color() ?? '${defaultColor}') : '${defaultColor}';
    ${colorMap}
    \$classes      = \$overrideClasses      ?? ${classExpr};
    \$iconClasses  = \$overrideIconClasses  ?? '${iconCls}';
    \$labelClasses = \$overrideLabelClasses ?? '${labelCls}';
@endphp
@include('${NS}::components._base.badge', [
    'case'         => \$case,
    'classes'      => \$classes,
    'iconClasses'  => \$iconClasses,
    'labelClasses' => \$labelClasses,
    'iconPosition' => \$iconPosition,
    'href'         => \$href,
    'ariaLabel'    => \$ariaLabel,
    'rootId'       => \$overrideRootId ?? null,
])
EOF
}
write_badge plain    "'enumerator-badge enumerator-badge-' . \$color"  'enumerator-icon' 'enumerator-label' 'default' ''
write_badge tailwind "sprintf('inline-flex items-center gap-x-1.5 rounded-full px-2 py-0.5 text-xs font-medium bg-%1\$s-50 text-%1\$s-700 ring-1 ring-inset ring-%1\$s-600/20', \$color)" 'size-3 shrink-0' '' 'gray' ''
write_badge daisyui  "'badge badge-' . \$color" 'size-[1em]' '' 'neutral' ''
write_badge bootstrap "'badge text-bg-' . \$color . ' rounded-pill'" 'me-1 align-middle' '' 'secondary' ''
write_badge bulma    "'tag is-' . \$color . ' is-rounded'" 'icon is-small mr-1' '' 'info' "\$color = ['error' => 'danger', 'destructive' => 'danger'][\$color] ?? \$color;"

# ---------------- SELECT ----------------
write_select() {
    local fw="$1" classes="$2"
    cat > "$(view_path "$fw" select)" <<EOF
@php
    \$cases ??= isset(\$enum) ? \$enum::cases() : [];
    \$valueOf ??= static fn (object \$c): string|int => \$c instanceof \BackedEnum ? \$c->value : \$c->name;
    \$labelOf ??= static fn (object \$c): string => method_exists(\$c, 'label') ? (string) \$c->label() : (string) (\$c->name ?? '');
    \$classes       = \$overrideClasses       ?? '${classes}';
    \$optionClasses = \$overrideOptionClasses ?? '';
@endphp
@include('${NS}::components._base.select', [
    'cases' => \$cases, 'name' => \$name, 'selectedValue' => \$selectedValue ?? null,
    'nullable' => \$nullable ?? false, 'placeholder' => \$placeholder ?? 'Select an option…',
    'multiple' => \$multiple ?? false, 'size' => \$size ?? null,
    'disabled' => \$disabled ?? false, 'required' => \$required ?? false,
    'classes' => \$classes, 'optionClasses' => \$optionClasses, 'groups' => \$groups ?? null,
    'valueOf' => \$valueOf, 'labelOf' => \$labelOf, 'ariaLabel' => \$ariaLabel ?? null,
    'rootId' => \$overrideRootId ?? null,
])
EOF
}
write_select plain    'enumerator-select'
write_select tailwind 'block w-full rounded-md border-0 py-1.5 pl-3 pr-10 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6'
write_select daisyui  'select select-bordered w-full'
write_select bootstrap 'form-select'
write_select bulma    'select is-fullwidth'

# ---------------- DROPDOWN ----------------
write_dropdown() {
    local fw="$1" wrapper="$2" labelCls="$3" descCls="$4" selectCls="$5"
    cat > "$(view_path "$fw" dropdown)" <<EOF
@php
    \$cases ??= isset(\$enum) ? \$enum::cases() : [];
    \$valueOf ??= static fn (object \$c): string|int => \$c instanceof \BackedEnum ? \$c->value : \$c->name;
    \$labelOf ??= static fn (object \$c): string => method_exists(\$c, 'label') ? (string) \$c->label() : (string) (\$c->name ?? '');
    \$wrapperClasses     = \$overrideWrapperClasses     ?? '${wrapper}';
    \$labelClasses       = \$overrideLabelClasses       ?? '${labelCls}';
    \$descriptionClasses = \$overrideDescriptionClasses ?? '${descCls}';
    \$classes            = \$overrideClasses            ?? '${selectCls}';
    \$optionClasses      = \$overrideOptionClasses      ?? '';
@endphp
@include('${NS}::components._base.dropdown', [
    'cases' => \$cases, 'name' => \$name, 'selectedValue' => \$selectedValue ?? null,
    'nullable' => \$nullable ?? false, 'placeholder' => \$placeholder ?? 'Select an option…',
    'multiple' => \$multiple ?? false, 'size' => \$size ?? null,
    'disabled' => \$disabled ?? false, 'required' => \$required ?? false,
    'classes' => \$classes, 'optionClasses' => \$optionClasses,
    'wrapperClasses' => \$wrapperClasses, 'labelClasses' => \$labelClasses, 'descriptionClasses' => \$descriptionClasses,
    'groups' => \$groups ?? null, 'valueOf' => \$valueOf, 'labelOf' => \$labelOf,
    'labelText' => \$labelText ?? null, 'description' => \$description ?? null,
    'ariaLabel' => \$ariaLabel ?? null, 'searchable' => \$searchable ?? false, 'clearable' => \$clearable ?? false,
    'rootId' => \$overrideRootId ?? null, 'wrapperId' => \$overrideWrapperId ?? null, 'labelId' => \$overrideLabelId ?? null,
])
EOF
}
write_dropdown plain    'enumerator-dropdown' 'enumerator-dropdown-label' 'enumerator-dropdown-description' 'enumerator-select'
write_dropdown tailwind 'space-y-1' 'block text-sm font-medium leading-6 text-gray-900' 'mt-1 text-xs text-gray-500' 'mt-2 block w-full rounded-md border-0 py-1.5 pl-3 pr-10 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6'
write_dropdown daisyui  'form-control w-full' 'label-text font-medium' 'label-text-alt text-base-content/60' 'select select-bordered w-full'
write_dropdown bootstrap 'mb-3' 'form-label fw-medium' 'form-text' 'form-select'
write_dropdown bulma    'field' 'label' 'help' 'select is-fullwidth'

# ---------------- RADIO ----------------
write_radio() {
    local fw="$1" root="$2" item="$3" input="$4" label="$5" legend="$6"
    cat > "$(view_path "$fw" radio)" <<EOF
@php
    \$cases ??= isset(\$enum) ? \$enum::cases() : [];
    \$valueOf ??= static fn (object \$c): string|int => \$c instanceof \BackedEnum ? \$c->value : \$c->name;
    \$labelOf ??= static fn (object \$c): string => method_exists(\$c, 'label') ? (string) \$c->label() : (string) (\$c->name ?? '');
    \$idFor ??= static fn (object \$c): string => \$name . '_' . preg_replace('/[^a-z0-9]+/i', '_', (string) \$valueOf(\$c));
    \$descriptionOf ??= static fn (object \$c): ?string => method_exists(\$c, 'description') ? \$c->description() : null;
    \$selectedValue = \$selectedValue ?? (\$selected ?? null);
    if (\$selectedValue instanceof \BackedEnum) { \$selectedValue = \$selectedValue->value; }
    elseif (\$selectedValue instanceof \UnitEnum) { \$selectedValue = \$selectedValue->name; }
    \$classes       = \$overrideClasses       ?? '${root}';
    \$itemClasses   = \$overrideItemClasses   ?? '${item}';
    \$inputClasses  = \$overrideInputClasses  ?? '${input}';
    \$labelClasses  = \$overrideLabelClasses  ?? '${label}';
    \$legendClasses = \$overrideLegendClasses ?? '${legend}';
@endphp
@include('${NS}::components._base.radio', [
    'cases' => \$cases, 'name' => \$name, 'selectedValue' => \$selectedValue,
    'layout' => \$layout ?? 'vertical', 'legend' => \$legend ?? null,
    'classes' => \$classes, 'itemClasses' => \$itemClasses, 'inputClasses' => \$inputClasses,
    'labelClasses' => \$labelClasses, 'legendClasses' => \$legendClasses,
    'disabled' => \$disabled ?? false, 'required' => \$required ?? false, 'descriptions' => \$descriptions ?? false,
    'valueOf' => \$valueOf, 'labelOf' => \$labelOf, 'idFor' => \$idFor, 'descriptionOf' => \$descriptionOf,
    'rootId' => \$overrideRootId ?? null,
])
EOF
}
write_radio plain    'enumerator-radio-group' 'enumerator-radio-item' '' 'enumerator-radio-label' 'enumerator-radio-legend'
write_radio tailwind 'space-y-3 data-[layout=horizontal]:flex data-[layout=horizontal]:flex-wrap data-[layout=horizontal]:gap-x-6 data-[layout=horizontal]:space-y-0' 'flex items-center gap-x-2' 'size-4 border-gray-300 text-indigo-600 focus:ring-2 focus:ring-indigo-600' 'text-sm font-medium leading-6 text-gray-900' 'text-sm font-semibold leading-6 text-gray-900'
write_radio daisyui  'space-y-2' 'form-control' 'radio radio-primary' 'label cursor-pointer gap-2 justify-start' 'label-text font-semibold'
write_radio bootstrap 'd-flex flex-column gap-2' 'form-check' 'form-check-input' 'form-check-label' 'form-label fw-semibold'
write_radio bulma    'field is-grouped is-grouped-multiline' 'control' 'mr-1' 'radio' 'label'

# ---------------- CHECKBOXES ----------------
write_checkboxes() {
    local fw="$1" root="$2" item="$3" input="$4" label="$5" legend="$6"
    cat > "$(view_path "$fw" checkboxes)" <<EOF
@php
    \$cases ??= isset(\$enum) ? \$enum::cases() : [];
    \$selectedValues ??= \$selected ?? [];
    if (! is_iterable(\$selectedValues)) { \$selectedValues = [\$selectedValues]; }
    \$normalized = [];
    foreach (\$selectedValues as \$v) {
        if (\$v instanceof \BackedEnum) { \$normalized[] = (string) \$v->value; }
        elseif (\$v instanceof \UnitEnum) { \$normalized[] = (string) \$v->name; }
        else { \$normalized[] = (string) \$v; }
    }
    \$selectedValues = \$normalized;
    \$valueOf ??= static fn (object \$c): string|int => \$c instanceof \BackedEnum ? \$c->value : \$c->name;
    \$labelOf ??= static fn (object \$c): string => method_exists(\$c, 'label') ? (string) \$c->label() : (string) (\$c->name ?? '');
    \$idFor ??= static fn (object \$c): string => rtrim(\$name, '[]') . '_' . preg_replace('/[^a-z0-9]+/i', '_', (string) \$valueOf(\$c));
    \$descriptionOf ??= static fn (object \$c): ?string => method_exists(\$c, 'description') ? \$c->description() : null;
    \$classes       = \$overrideClasses       ?? '${root}';
    \$itemClasses   = \$overrideItemClasses   ?? '${item}';
    \$inputClasses  = \$overrideInputClasses  ?? '${input}';
    \$labelClasses  = \$overrideLabelClasses  ?? '${label}';
    \$legendClasses = \$overrideLegendClasses ?? '${legend}';
@endphp
@include('${NS}::components._base.checkboxes', [
    'cases' => \$cases, 'name' => \$name, 'selectedValues' => \$selectedValues,
    'layout' => \$layout ?? 'vertical', 'legend' => \$legend ?? null,
    'classes' => \$classes, 'itemClasses' => \$itemClasses, 'inputClasses' => \$inputClasses,
    'labelClasses' => \$labelClasses, 'legendClasses' => \$legendClasses,
    'disabled' => \$disabled ?? false, 'required' => \$required ?? false, 'descriptions' => \$descriptions ?? false,
    'valueOf' => \$valueOf, 'labelOf' => \$labelOf, 'idFor' => \$idFor, 'descriptionOf' => \$descriptionOf,
    'rootId' => \$overrideRootId ?? null,
])
EOF
}
write_checkboxes plain    'enumerator-checkbox-group' 'enumerator-checkbox-item' '' 'enumerator-checkbox-label' 'enumerator-checkbox-legend'
write_checkboxes tailwind 'space-y-3 data-[layout=horizontal]:flex data-[layout=horizontal]:flex-wrap data-[layout=horizontal]:gap-x-6 data-[layout=horizontal]:space-y-0' 'flex items-center gap-x-2' 'size-4 rounded border-gray-300 text-indigo-600 focus:ring-2 focus:ring-indigo-600' 'text-sm font-medium leading-6 text-gray-900' 'text-sm font-semibold leading-6 text-gray-900'
write_checkboxes daisyui  'space-y-2' 'form-control' 'checkbox checkbox-primary' 'label cursor-pointer gap-2 justify-start' 'label-text font-semibold'
write_checkboxes bootstrap 'd-flex flex-column gap-2' 'form-check' 'form-check-input' 'form-check-label' 'form-label fw-semibold'
write_checkboxes bulma    'field is-grouped is-grouped-multiline' 'control' 'mr-1' 'checkbox' 'label'

# ---------------- GRID ----------------
write_grid() {
    local fw="$1" root="$2" item="$3" label="$4" desc="$5"
    cat > "$(view_path "$fw" grid)" <<EOF
@php
    \$cases ??= isset(\$enum) ? \$enum::cases() : [];
    \$labelOf ??= static fn (object \$c): string => method_exists(\$c, 'label') ? (string) \$c->label() : (string) (\$c->name ?? '');
    \$descriptionOf ??= static fn (object \$c): ?string => method_exists(\$c, 'description') ? \$c->description() : null;
    \$valueOf ??= static fn (object \$c): string|int => \$c instanceof \BackedEnum ? \$c->value : \$c->name;
    \$classes            = \$overrideClasses            ?? '${root}';
    \$itemClasses        = \$overrideItemClasses        ?? '${item}';
    \$labelClasses       = \$overrideLabelClasses       ?? '${label}';
    \$descriptionClasses = \$overrideDescriptionClasses ?? '${desc}';
@endphp
@include('${NS}::components._base.grid', [
    'cases' => \$cases, 'columns' => \$columns ?? 3, 'showBadges' => \$showBadges ?? false,
    'classes' => \$classes, 'itemClasses' => \$itemClasses, 'labelClasses' => \$labelClasses,
    'descriptionClasses' => \$descriptionClasses,
    'labelOf' => \$labelOf, 'descriptionOf' => \$descriptionOf, 'valueOf' => \$valueOf,
    'rootId' => \$overrideRootId ?? null,
])
EOF
}
write_grid plain    'enumerator-grid' 'enumerator-grid-item' 'enumerator-grid-label' 'enumerator-grid-description'
write_grid tailwind 'grid gap-4 data-[columns="1"]:grid-cols-1 data-[columns="2"]:grid-cols-2 data-[columns="3"]:grid-cols-3 data-[columns="4"]:grid-cols-4 data-[columns="5"]:grid-cols-5 data-[columns="6"]:grid-cols-6' 'rounded-lg bg-white p-4 ring-1 ring-gray-200 shadow-sm' 'text-sm font-semibold text-gray-900' 'mt-1 text-sm text-gray-600'
write_grid daisyui  'grid gap-4' 'card card-bordered bg-base-100 shadow-sm card-md' 'card-title text-base' 'text-sm text-base-content/70'
write_grid bootstrap 'row g-3 row-cols-1 row-cols-md-2 row-cols-lg-3' 'col card h-100 p-3 border-0 shadow-sm' 'card-title h6 mb-1' 'card-text small text-body-secondary'
write_grid bulma    'columns is-multiline is-variable is-3' 'column is-one-third box' 'title is-6 mb-2' 'subtitle is-7'

# ---------------- LISTING ----------------
write_listing() {
    local fw="$1" root="$2" item="$3"
    cat > "$(view_path "$fw" listing)" <<EOF
@php
    \$cases ??= isset(\$enum) ? \$enum::cases() : [];
    \$labelOf ??= static fn (object \$c): string => method_exists(\$c, 'label') ? (string) \$c->label() : (string) (\$c->name ?? '');
    \$valueOf ??= static fn (object \$c): string|int => \$c instanceof \BackedEnum ? \$c->value : \$c->name;
    \$classes     = \$overrideClasses     ?? '${root}';
    \$itemClasses = \$overrideItemClasses ?? '${item}';
@endphp
@include('${NS}::components._base.list', [
    'cases' => \$cases, 'classes' => \$classes, 'itemClasses' => \$itemClasses,
    'labelOf' => \$labelOf, 'valueOf' => \$valueOf,
    'rootId' => \$overrideRootId ?? null,
])
EOF
}
write_listing plain    'enumerator-list' 'enumerator-list-item'
write_listing tailwind 'divide-y divide-gray-200' 'py-2 text-sm text-gray-700'
write_listing daisyui  'menu bg-base-100 rounded-box' 'menu-item'
write_listing bootstrap 'list-group list-group-flush' 'list-group-item'
write_listing bulma    'panel' 'panel-block'

# ---------------- ELEMENT ----------------
write_element() {
    local fw="$1" root="$2"
    cat > "$(view_path "$fw" element)" <<EOF
@php
    \$classes      = \$overrideClasses      ?? '${root}';
    \$iconClasses  = \$overrideIconClasses  ?? null;
    \$labelClasses = \$overrideLabelClasses ?? null;
    \$badgeClasses = \$overrideBadgeClasses ?? null;
@endphp
@include('${NS}::components._base.element', [
    'case' => \$case, 'as' => \$as ?? 'span', 'href' => \$href ?? null, 'type' => \$type ?? null, 'for' => \$for ?? null,
    'showIcon' => \$showIcon ?? false, 'showLabel' => \$showLabel ?? true, 'showBadge' => \$showBadge ?? false,
    'iconClasses' => \$iconClasses, 'labelClasses' => \$labelClasses, 'badgeClasses' => \$badgeClasses, 'classes' => \$classes,
    'rootId' => \$overrideRootId ?? null,
    'slot' => \$slot ?? '',
])
EOF
}
write_element plain    'enumerator-element'
write_element tailwind 'inline-flex items-center gap-x-1.5'
write_element daisyui  'inline-flex items-center gap-2'
write_element bootstrap 'd-inline-flex align-items-center gap-2'
write_element bulma    'is-inline-flex is-align-items-center'

echo "==> 40 self-sufficient framework variant views written"
