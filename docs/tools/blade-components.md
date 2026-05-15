# Blade components

Nine components ship with the package:

| Tag | Element | Purpose |
|---|---|---|
| `<x-laranail-enumerator::badge>` | `<span>` | Render a single case as a coloured badge |
| `<x-laranail-enumerator::element>` | `<a>` / `<button>` / `<span>` | Render a single case as an interactive element |
| `<x-laranail-enumerator::select>` | `<select>` | Native select with one `<option>` per case |
| `<x-laranail-enumerator::dropdown>` | Alpine-driven | Searchable / clearable dropdown (requires `<x-...::alpine-loader />` — landing in PR-G) |
| `<x-laranail-enumerator::radio>` | `<fieldset><input type="radio">` | Radio group |
| `<x-laranail-enumerator::checkboxes>` | `<fieldset><input type="checkbox">` | Checkbox group |
| `<x-laranail-enumerator::grid>` | `<div>` grid | Grid of badge-like tiles |
| `<x-laranail-enumerator::listing>` | `<ul>` | Flat list |
| `<x-laranail-enumerator::alpine-loader>` | `<script>` | CDN-first Alpine.js loader for the Alpine-enhanced components — see [`alpine-loader.md`](alpine-loader.md) |

```blade
<x-laranail-enumerator::badge :case="$user->status" />
<x-laranail-enumerator::select :enum="UserStatusEnum::class" name="status" />
<x-laranail-enumerator::radio :enum="UserStatusEnum::class" name="status" layout="horizontal" />
<x-laranail-enumerator::checkboxes :enum="PermissionEnum::class" name="permissions[]" :selected="$selected" />
<x-laranail-enumerator::grid :enum="UserStatusEnum::class" :columns="3" />
<x-laranail-enumerator::listing :enum="UserStatusEnum::class" />
```

Each component picks the view bundle based on
`config('enumerator.css_framework')` (overridable via `framework=` prop).

## Frameworks

Five framework variants ship per component (where styling applies):
`plain`, `tailwind`, `daisyui`, `bootstrap`, `bulma`. Publish the
view bundle to customise:

```bash
php artisan vendor:publish --tag=enumerator-views
```

`<x-laranail-enumerator::alpine-loader>` is framework-agnostic.

## Livewire integration

The `select`, `radio`, and `checkboxes` components forward arbitrary
HTML attributes (anything not matching a known component prop)
through to their outer element. That makes Livewire bindings work
out of the box:

```blade
{{-- wire:model.live on the <select> --}}
<x-laranail-enumerator::select
    :enum="UserStatusEnum::class"
    name="status"
    wire:model.live="status"
/>

{{-- wire:model on the radio <fieldset>; Livewire 3 propagates to
     the child <input>s via DOM morph --}}
<x-laranail-enumerator::radio
    :enum="UserStatusEnum::class"
    name="status"
    wire:model="status"
/>

{{-- Same for checkboxes — the wire:model is placed on the
     <fieldset>. For per-input Livewire binding (e.g. array-bound
     checkboxes), see the v0.3.0 backlog (input-level wireModel
     prop). --}}
<x-laranail-enumerator::checkboxes
    :enum="PermissionEnum::class"
    name="permissions[]"
    wire:model="permissions"
/>
```

Any `data-*`, `aria-*`, `x-*` (Alpine), `wire:*` (Livewire), or
custom attributes you set on the component tag flow through to the
outer element. Laravel's `ComponentAttributeBag` HTML-escapes
attribute values at serialisation time, so the forwarded string is
safe under normal use.

## Known component props (NOT forwarded)

Each component reserves a set of prop names — those are consumed by
the PHP component class and do NOT flow through the
attribute-forwarding path. The reserved sets:

- **select**: `class`, `id`, `enum`, `name`, `selected`, `nullable`,
  `placeholder`, `multiple`, `size`, `disabled`, `required`,
  `framework`, `groups-by`, `groups`, `group-labels`, `aria-label`,
  `classes`, `option-classes`, `root-id`
- **radio**: `class`, `id`, `enum`, `name`, `selected`, `layout`,
  `legend`, `framework`, `disabled`, `required`, `descriptions`,
  `classes`, `item-classes`, `input-classes`, `label-classes`,
  `legend-classes`, `root-id`
- **checkboxes**: same as radio (with `selected` accepting an iterable)

If you need to set one of those names as a raw HTML attribute (rare),
use a `:slot` or wrap the component manually.

## Class merging

Set `class="..."` on the component tag and your classes append after
the framework's defaults — yours win the cascade:

```blade
<x-laranail-enumerator::badge :case="$user->status" class="ml-2 shadow-sm" />
```

## See also

- [Blade directives](blade-directives.md) — `@enumeratorLabel`, `@enumeratorBadge`, …
- [Alpine.js loader](alpine-loader.md) — needed for the
  Alpine-enhanced `<x-...::dropdown>`
- [`docs/recipes/livewire.md`](../recipes/livewire.md) — recipe with
  a full Livewire form using the components
