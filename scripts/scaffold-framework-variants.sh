#!/usr/bin/env bash
# scripts/scaffold-framework-variants.sh
#
# Generates per-framework Blade variant components. Each variant is a thin
# anonymous component that delegates to the namespaced class component with
# `framework="..."` pre-set. Consumers can use whichever style they prefer:
#
#   <x-laranail-enumerator::badge :case="..." framework="tailwind" />
#   <x-laranail-enumerator::tailwind.badge :case="..." />
#
# The second form is sugar — both render identical HTML. Variants ship for:
# plain, tailwind, daisyui, bootstrap, bulma.
#
# Components covered per framework:
#   badge, select, dropdown, radio, checkboxes, grid, listing, element
#
# Idempotent — re-running overwrites existing variant files only (does not
# touch base/_base/).

set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

FRAMEWORKS=(plain tailwind daisyui bootstrap bulma)
NS="laranail-enumerator"

write_variant() {
    local fw="$1" name="$2" body="$3"
    cat > "resources/views/components/${fw}/${name}.blade.php" <<EOF
${body}
EOF
}

for fw in "${FRAMEWORKS[@]}"; do
    mkdir -p "resources/views/components/${fw}"

    # Badge — forwards :case and all attrs.
    write_variant "$fw" badge \
"{{-- ${fw} badge — delegates to the namespaced class component with framework preset. --}}
<x-${NS}::badge :case=\"\$case\" framework=\"${fw}\" :icon-position=\"\$iconPosition ?? 'before'\" :href=\"\$href ?? null\" :aria-label=\"\$ariaLabel ?? null\" {{ \$attributes }}>
    {{ \$slot ?? '' }}
</x-${NS}::badge>"

    # Select.
    write_variant "$fw" select \
"{{-- ${fw} select. --}}
<x-${NS}::select
    :enum=\"\$enum\"
    :name=\"\$name\"
    :selected=\"\$selected ?? null\"
    :nullable=\"\$nullable ?? false\"
    :placeholder=\"\$placeholder ?? 'Select an option…'\"
    :multiple=\"\$multiple ?? false\"
    :size=\"\$size ?? null\"
    :disabled=\"\$disabled ?? false\"
    :required=\"\$required ?? false\"
    :groups-by=\"\$groupsBy ?? null\"
    :groups=\"\$groups ?? null\"
    :group-labels=\"\$groupLabels ?? []\"
    :aria-label=\"\$ariaLabel ?? null\"
    framework=\"${fw}\"
    {{ \$attributes }}
/>"

    # Dropdown.
    write_variant "$fw" dropdown \
"{{-- ${fw} dropdown. --}}
<x-${NS}::dropdown
    :enum=\"\$enum\"
    :name=\"\$name\"
    :selected=\"\$selected ?? null\"
    :nullable=\"\$nullable ?? true\"
    :placeholder=\"\$placeholder ?? 'Select an option…'\"
    :multiple=\"\$multiple ?? false\"
    :size=\"\$size ?? null\"
    :disabled=\"\$disabled ?? false\"
    :required=\"\$required ?? false\"
    :groups-by=\"\$groupsBy ?? null\"
    :groups=\"\$groups ?? null\"
    :group-labels=\"\$groupLabels ?? []\"
    :aria-label=\"\$ariaLabel ?? null\"
    :label-text=\"\$labelText ?? null\"
    :description=\"\$description ?? null\"
    :searchable=\"\$searchable ?? false\"
    :clearable=\"\$clearable ?? false\"
    framework=\"${fw}\"
    {{ \$attributes }}
/>"

    # Radio.
    write_variant "$fw" radio \
"{{-- ${fw} radio group. --}}
<x-${NS}::radio
    :enum=\"\$enum\"
    :name=\"\$name\"
    :selected=\"\$selected ?? null\"
    :layout=\"\$layout ?? 'vertical'\"
    :legend=\"\$legend ?? null\"
    :disabled=\"\$disabled ?? false\"
    :required=\"\$required ?? false\"
    :descriptions=\"\$descriptions ?? false\"
    framework=\"${fw}\"
    {{ \$attributes }}
/>"

    # Checkboxes.
    write_variant "$fw" checkboxes \
"{{-- ${fw} checkbox group. --}}
<x-${NS}::checkboxes
    :enum=\"\$enum\"
    :name=\"\$name\"
    :selected=\"\$selected ?? []\"
    :layout=\"\$layout ?? 'vertical'\"
    :legend=\"\$legend ?? null\"
    :disabled=\"\$disabled ?? false\"
    :required=\"\$required ?? false\"
    :descriptions=\"\$descriptions ?? false\"
    framework=\"${fw}\"
    {{ \$attributes }}
/>"

    # Grid.
    write_variant "$fw" grid \
"{{-- ${fw} grid. --}}
<x-${NS}::grid
    :enum=\"\$enum\"
    :columns=\"\$columns ?? 3\"
    :show-badges=\"\$showBadges ?? false\"
    framework=\"${fw}\"
    {{ \$attributes }}
/>"

    # Listing (replaces the former 'list').
    write_variant "$fw" listing \
"{{-- ${fw} listing. --}}
<x-${NS}::listing
    :enum=\"\$enum\"
    framework=\"${fw}\"
    {{ \$attributes }}
/>"

    # Element.
    write_variant "$fw" element \
"{{-- ${fw} polymorphic element. --}}
<x-${NS}::element
    :case=\"\$case\"
    :as=\"\$as ?? 'span'\"
    :href=\"\$href ?? null\"
    :type=\"\$type ?? null\"
    :for=\"\$for ?? null\"
    :show-icon=\"\$showIcon ?? false\"
    :show-label=\"\$showLabel ?? true\"
    :show-badge=\"\$showBadge ?? false\"
    framework=\"${fw}\"
    {{ \$attributes }}
>
    {{ \$slot ?? '' }}
</x-${NS}::element>"
done

echo "==> Framework variant components written:"
for fw in "${FRAMEWORKS[@]}"; do
    echo "  ${fw}/: $(ls resources/views/components/${fw}/ | wc -l) files"
done
