# Blade components

Nine components ship with the package:

| Tag | Element | Purpose |
|---|---|---|
| `<x-laranail-enumerator::badge>` | `<span>` | Render a single case as a coloured badge |
| `<x-laranail-enumerator::element>` | `<a>` / `<button>` / `<span>` | Render a single case as an interactive element |
| `<x-laranail-enumerator::select>` | `<select>` | Native select with one `<option>` per case |
| `<x-laranail-enumerator::dropdown>` | Alpine-driven combobox / native `<select>` | Searchable / clearable when `:searchable="true"` or `:clearable="true"` — Alpine combobox. Otherwise — native `<select>`. Alpine path requires `<x-...::alpine-loader />`. |
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

Two layers:

**Attribute-bag forwarding** — every component forwards arbitrary
HTML attributes (anything not matching a known prop) to the outer
element. Works for `wire:*`, `data-*`, `aria-*`, `x-*`, etc.:

```blade
{{-- wire:model.live on the <select> --}}
<x-laranail-enumerator::select
    :enum="UserStatusEnum::class"
    name="status"
    wire:model.live="status"
/>
```

For `<x-...::select>` that's enough: Livewire binds at the `<select>`
element directly. But for radio + checkboxes, Livewire 3 binds at
the `<input>` level — placing `wire:model` on the wrapping
`<fieldset>` works for radios (via Livewire's DOM-morph) but **fails
for checkbox-array binding**.

**Per-input `:wireModel` prop** — for radio + checkboxes, the
dedicated `wireModel` / `wireModelModifier` props emit `wire:model`
on every `<input>`:

```blade
{{-- wire:model="status" emitted on each radio input --}}
<x-laranail-enumerator::radio
    :enum="UserStatusEnum::class"
    name="status"
    wire-model="status"
/>

{{-- wire:model.live="status" on each input (with the .live modifier) --}}
<x-laranail-enumerator::radio
    :enum="UserStatusEnum::class"
    name="status"
    wire-model="status"
    wire-model-modifier="live"
/>

{{-- Required shape for Livewire array-bound checkboxes --}}
<x-laranail-enumerator::checkboxes
    :enum="PermissionEnum::class"
    name="permissions[]"
    wire-model="permissions"
/>

{{-- Complex modifiers work too --}}
<x-laranail-enumerator::checkboxes
    :enum="PermissionEnum::class"
    name="permissions[]"
    wire-model="permissions"
    wire-model-modifier="debounce.500ms"
/>
```

Laravel's `ComponentAttributeBag` HTML-escapes attribute values at
serialisation time, so attribute-bag forwarding is safe under normal
use. The `:wireModel` prop value is interpolated directly into the
attribute — pass only a Livewire property name (it should never come
from user input).

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

## Dropdown — Alpine-enhanced searchable / clearable

`<x-laranail-enumerator::dropdown>` ships in two shapes:

```blade
{{-- Native <select> path (v0.1.0 behaviour). Form submits via the
     <select> directly. data-searchable / data-clearable attrs remain
     for third-party JS libraries (Tom Select, Choices.js, etc.). --}}
<x-laranail-enumerator::dropdown :enum="UserStatusEnum::class" name="status" />

{{-- Alpine combobox path. Requires Alpine.js in the page — drop in
     <x-laranail-enumerator::alpine-loader /> once in your layout. --}}
<x-laranail-enumerator::dropdown
    :enum="UserStatusEnum::class"
    name="status"
    :selected="$user->status"
    :searchable="true"
    :clearable="true"
    label-text="Status"
    description="Used by the dashboard filter."
/>
```

### Alpine combobox features

When `:searchable="true"` OR `:clearable="true"`, the dropdown emits:

- A button that toggles the panel and shows the current selection
- A hidden `<input type="hidden" name="...">` that carries the value
  to form submission
- An optional search filter input (when `:searchable="true"`)
- An optional clear button (when `:clearable="true"`)
- A `<ul role="listbox">` with one `<li role="option">` per case
- Keyboard navigation: `↓` / `↑` to move focus, `Enter` to commit,
  `Esc` to close
- An empty-state row ("No matches.") when filter yields zero results
- Click-outside-to-close + `Esc` global close
- A `change` event dispatched on selection (`x-on:change` works)

### Multi-select mode (PR-δ, v0.3.0)

When `:multiple="true"` is combined with `:searchable="true"` or
`:clearable="true"`, the Alpine path stays engaged and shifts into
multi-select shape:

- The trigger renders a **pill UI** — one `<span
  class="enumerator-dropdown-pill">` per selected value, each with
  an `×` button to remove that value.
- Hidden inputs are emitted via `<template x-for>` over the selected
  values — one `<input type="hidden" name="permissions[]"
  :value="..."` per selection. PHP receives the array shape Eloquent
  / FormRequest expects.
- `commitSelection` toggles values (add if absent, remove if
  present) and **keeps the panel open** so consecutive selections
  don't require re-opening.
- The listbox `<ul>` gets `aria-multiselectable="true"`; each
  `<li>`'s `aria-selected` flips per selection.
- The clear button (when `:clearable="true"`) empties the array
  via `clearSelection()`.

```blade
<x-laranail-enumerator::dropdown
    :enum="PermissionEnum::class"
    name="permissions[]"
    :multiple="true"
    :searchable="true"
    :clearable="true"
    :selected="$user->permissions"
    label-text="Permissions"
/>
```

`disabled=true` always falls through to the native
`<select multiple>` — the listbox doesn't run on disabled fields.

### Setup

Drop the loader into your layout once:

```blade
<x-laranail-enumerator::alpine-loader />
```

See [`alpine-loader.md`](alpine-loader.md) for CDN / local-fallback
configuration, CSP, and opt-out flags.

### Styling

The Alpine combobox emits semantic classes you can style:

- `.enumerator-dropdown` — the outer wrapper `<div>` (also used in the
  native path)
- `.enumerator-dropdown-combobox` — the Alpine root
- `.enumerator-dropdown-button` — the trigger
- `.enumerator-dropdown-clear` — the clear button
- `.enumerator-dropdown-panel` — the floating panel (hidden by default
  via inline `style="display:none"` until Alpine evaluates `x-show`)
- `.enumerator-dropdown-search` — the filter input
- `.enumerator-dropdown-list` — the `<ul role="listbox">`
- `.enumerator-dropdown-option` — each `<li role="option">`
- `.enumerator-dropdown-active` — added to the currently-hovered /
  arrow-keyed option
- `.enumerator-dropdown-empty` — the "No matches." row

The package ships no CSS. Style these classes in your app or via the
publishable view bundle.

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
