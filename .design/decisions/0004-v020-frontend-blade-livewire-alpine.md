# ADR-0004 — v0.2.0 frontend surface: Blade + Livewire + Alpine

- **Status:** Accepted
- **Date:** 2026-05-15
- **Deciders:** Imani Manyara

## Context

v0.1.0 ships Blade components (8 components × 5 framework variants) and
Blade directives (11). It does NOT ship Livewire-aware components, nor
any JS enhancement. No other surveyed Laravel enum package ships
Livewire-aware components — that's a credible differentiator.

Pre-flight question Phase 1.7 offered four scopes; user picked
"Blade + Livewire + Alpine".

## Decision

v0.2.0 ships, in this order:

1. **Alpine loader infrastructure** (ADR-0005) — Blade component plus
   loader logic that supports CDN-first with local fallback, conflict
   detection, no-double-load.
2. **Livewire-aware Blade components** — `<x-...::select>`,
   `<x-...::radio>`, `<x-...::checkboxes>` accept Livewire `wire:model`
   bindings, integrate with Livewire form validation, and respect
   `wire:loading` states.
3. **Alpine-enhanced `<x-...::dropdown>`** — searchable, clearable,
   keyboard-navigable. Replaces the v0.1.0 `<x-...::select>` for
   richer interaction; the bare `<x-...::select>` remains for
   pages without JS.

The Livewire state-transition Action helper is **deferred** to v0.3.0
as a recipe (see feature-gap F-15).

## Consequences

- Livewire becomes a `require-dev` so CI can exercise the components
  (ADR-0006). Not a `require` — the package remains usable without
  Livewire by design.
- A new top-level `resources/js/` directory ships with the Alpine
  fallback file, publishable via `--tag=enumerator-js`.
- The Phase 1 Blade view naming drift fix (I-3,
  `_base/list.blade.php` → `listing.blade.php`) lands first so the
  Livewire-aware components inherit the corrected name from day one.
- Coverage may dip transiently if the new components don't ship with
  immediate coverage parity. The CI gate stays at `--min=80` (per
  ADR-0007 below) to absorb that.

## Alternatives considered

- **Blade only** (v0.1.0 posture): Lowest risk but no v0.2.0
  differentiator over the published landscape.
- **Blade + Alpine only, no Livewire**: Loses the most credible
  differentiator vs. the published landscape.
- **Full Livewire state-transition helper**: Out of scope for v0.2.0;
  documented in v0.3.0 backlog.
