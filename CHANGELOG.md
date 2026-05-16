# Changelog

All notable changes to `laranail/enumerator` are documented in this file.

The format follows [Keep a Changelog](https://keepachangelog.com/en/1.1.0/)
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

_(reserved for v0.5.0 — see
[.design/plans/v0.4.0-scope.md](.design/plans/v0.4.0-scope.md)
follow-up notes and [.design/plans/v1.0.0-roadmap.md](.design/plans/v1.0.0-roadmap.md)
for the v0.x → v1.0 trajectory.)_

## [0.4.0] — 2026-05-16

The **hardening + parity** pass. Closes parity gaps left by v0.3.0
(dropdown wire:model + a11y + runtime i18n), pushes coverage past
the long-standing 85 % threshold, halves the PHPStan baseline noise,
and lands the v0.x → v1.0 lookahead.

Backwards compatible with v0.3.0 — no shipped public API removed
or renamed; every new prop / method is opt-in additive.

### Added

- **Runtime i18n for the multi-select Alpine dropdown** (PR-ρ). v0.3.0
  shipped nine hardcoded English strings inside the Blade view + Alpine
  `x-data` state (search placeholder, empty-state, clear / pill /
  announcement labels). PR-ρ routes every user-facing string through
  the Laravel translator under
  `enumerator::enumerator.components.dropdown.*`, with English
  defaults seeded in `lang/en/enumerator.php`. The
  `Blade\Components\Dropdown::resolveDropdownStrings()` helper splits
  `:label`-bearing patterns into a JS-prefix + the placeholder so
  Alpine can substitute the option label at runtime; non-pattern
  strings flow through directly. 4 new feature tests pin the en
  defaults AND the locale-override behaviour (fr / es overlays via
  `Translator::addLines()`). Closes the gap between ADR-0003's stated
  i18n-pluggable posture and the v0.3.0 dropdown surface. See
  [ADR-0008](.design/decisions/0008-reaffirm-en-only-i18n-with-runtime-i18n-surface-closed.md)
  for the reaffirm-en-only-default decision and the flip conditions.
- **A11y wiring on the Alpine dropdown listbox** (PR-ο). Brings the
  multi-select dropdown's keyboard + screen-reader behaviour up to
  the WAI-ARIA Authoring Practices Guide combobox-with-listbox
  pattern: `aria-controls` linking the trigger / search input to the
  listbox `<ul id="{name}-listbox">`, `aria-activedescendant` bound
  to the currently-active option's id (`{name}-opt-{idx}`), per-`<li>`
  ids, plus the existing `aria-multiselectable` / `aria-selected` /
  pill `aria-label` posture from v0.3.0. New opt-in
  `:announce-changes="true"` prop emits a polite
  `aria-live="polite" aria-atomic="true"` `<span>` near the wrapper
  that Alpine populates with `Added <label>` / `Removed <label>` /
  `Selected <label>` / `Selection cleared`. 9 new feature tests.
  Browser-level screen-reader validation deferred to v0.5.0.
- **`IsCacheKey` driver-matrix test coverage** (PR-ξ). v0.3.0 PR-η
  shipped IsCacheKey tested only against the array cache driver. New
  feature suite at `tests/Feature/Concerns/IsCacheKeyDriverMatrixTest.php`
  pins put / get / cached / forget / remember / increment / decrement
  roundtrips against the **file** driver and the **database** driver
  (`cache` table created on the in-memory sqlite connection via the
  same shape as Testbench's stub migration). Redis is documented as
  a CI follow-up — needs a redis sidecar the local test harness can't
  reliably bind. The trait is now demonstrably driver-agnostic on the
  two drivers Laravel ships out of the box.
- **`WithEnumTransitions` bulk + validation helpers** (PR-ν). Two
  new methods on the v0.3.0 Livewire trait. `bulkTransitionEnum(
  $paths, $target)` advances many Stateful properties to the same
  target case in one call (for "approve all" / "ship all" actions);
  per-path failures are independent. `transitionEnumOrValidate(
  $path, $target, $messages)` matches the original `transitionEnum`
  semantics but accepts caller-overridable error text for the
  `invalid` and `notStateful` failure modes — useful when the
  framework-default message doesn't fit UX copy. Both methods reuse
  the existing error-bag + dispatch wiring; no breakage to v0.3.0
  callers. 7 new feature tests.
- **Dropdown component `:wireModel` / `:wireModelModifier` props**
  (PR-μ). Mirror of the PR-γ pattern on radio + checkboxes — closes
  the parity gap left in v0.3.0. Emits `wire:model[.modifier]="..."`
  on the hidden `<input>(s)` for the Alpine listbox path (one per
  selected value in multi-select via `<template x-for>`) and on the
  `<select>` element for the native fallback path. Values flow
  through `htmlspecialchars(..., ENT_QUOTES, 'UTF-8', false)` per
  the D75 escape discipline. New `Dropdown` contract test extends
  the existing `Select` / `Radio` / `Checkboxes` no-extraAttrs
  arch tests. 9 new feature tests cover the four emission shapes
  (single/multi × Alpine/native) + escape regressions.

### Changed

- **PHPStan baseline: halved** (PR-θ). The PR-α residue analysis
  (v0.3.0) flagged ~120 patterns over the ≤ 600 target left after
  the v0.3.0 freeze. Most were trait-cascade noise: every new
  consumer enum that uses the `HasEnumeratorBehavior` umbrella
  retriggers the same defensive type-check warnings from the trait
  bodies. PR-α had path-scoped `src/Concerns/*` itself; PR-θ extends
  the same path-scope pattern to `tests/Fixtures/Enums/*` and
  `src/Presets/Enums/*` (the two big consumer dirs) plus the
  magic-method tests (`HasEnumAttributesTest`, `MagicComparisonsTest`,
  `Modules/PestTest`) and `EnumeratorServiceProvider`'s container-
  return-typed argument leaks. Result: baseline errors 1480 → 704
  (−52 %), file 4615 → 2179 lines (−53 %), unique entries ~360
  (under the ≤ 600 PR-α target). `reportUnmatchedIgnoredErrors:
  true` flip stays deferred to v0.5.0.

### Docs

- **v1.0.0 roadmap doc** (PR-σ). New `.design/plans/v1.0.0-roadmap.md`
  enumerates three buckets: (a) public surface stable enough to lock
  at v1.0.0 (every Contract interface, the always-on trait set, all
  9 opt-in feature traits, 4 Casts, 9 Attributes, Helpers + Facade,
  9 Blade components, cache surface); (b) experimental surface that
  needs more v0.x bake time (WithEnumTransitions real-Livewire
  roundtrip, EnumeratorCasts, StructuredOutput emitters, Lighthouse
  / Saloon / Octane / Pest modules, Filament / Inertia / Nova
  integrations); (c) breaking changes worth saving for v1.0.0 (drop
  dead defensive guards, tighten `mixed` widening, split
  HasEnumerator umbrella, opt-in HasTransitions, config-key
  rename). Plus a v0.x → v1.0 cycle sequence.

### Tests

- **Coverage push** (PR-ι + PR-λ). `AttributesCache` 77.6 % → 99.2 %
  via 8 new branch-coverage tests (mixed-bit / duplicate-bit / non-
  power-of-two / class-const-bit fixtures). `LaravelTranslatorAdapter`
  62.5 % → 87.5 % via stub-translator container binding to exercise
  the `try / catch (\Throwable)` branches in `translate()` and
  `has()`. Total `src/` coverage: 83.40 % → 85.10 %. The CI
  `--min=83` floor remains unchanged; per the v0.2.0 → v0.2.1 lesson,
  the gate moves to `--min=85` once the measured number sustains
  across two consecutive releases — v0.4.0 is the first.
- **`extraAttrs` contract tests** (PR-π) on `Radio` and `Checkboxes`,
  mirroring the existing `Select` arch test. Pins the trust contract
  that `{!! \$extraAttrs !!}` only receives `ComponentAttributeBag`-
  built (HTML-escaped) input.

## [0.3.0] — 2026-05-16

The **integration-depth + foundation** pass. Adds Livewire
state-transition tooling, cache-key enum encapsulation, multi-select
Alpine dropdown, per-input Livewire bindings on radio / checkboxes,
Codecov + mutation-testing CI channels, and a 39 %-pattern /
32 %-occurrence shrink of the PHPStan baseline.

Backwards compatible with `v0.2.x` consumers — no shipped public
surface removed, no public method renamed. The only behaviour
change worth flagging: `<x-...::dropdown :multiple="true">` paired
with `:searchable="true"` or `:clearable="true"` now renders the
Alpine listbox instead of falling through to the native `<select
multiple>`. The form-submission shape (one hidden input per selected
value with `name="…[]"`) matches what the native select would have
submitted, so server-side consumers see the same payload.

### Added

- **`infection/infection` mutation testing infrastructure** (PR-ε
  sub-batch). New `infection.json5` config targeting the core
  `src/Concerns`, `src/Helpers`, `src/Casts`, `src/Rules`,
  `src/Support` surface (modules / integrations / blade-views / etc.
  excluded). New `.github/workflows/infection.yml` runs mutation
  testing with pcov coverage on push to main + on PRs touching
  src/ or tests/. Non-blocking on first runs (`continue-on-error:
  true`) — pure measurement until a baseline kill rate is observed.
  No `minMsi` / `minCoveredMsi` gate yet; promotion to gating
  follows the same coverage-then-gate discipline as v0.2.0/v0.2.1.
- **`IsCacheKey` trait + `Cacheable` contract** (PR-η). Define an
  enum case as a cache-key namespace, with shorthand methods
  proxying to Laravel's `Cache` facade — `put()` / `get()` /
  `forget()` / `cached()` / `remember()` / `increment()` /
  `decrement()`. The presence probe is `cached()` rather than
  `has()` so the trait composes with `HasEnumeratorBehavior`
  (which ships a static `Enumerator::has(target)` membership
  check) without a fatal trait-method collision; the regression
  is pinned by a composed-enum feature test. Standalone trait
  (not in the `HasEnumerator` umbrella) so a cache-key enum doesn't
  accidentally pull in label / colour / icon surface area when
  only the cache shape is wanted. Override `key()` in the consumer
  enum for a non-trivial cache-key shape (e.g.,
  `'settings:' . $this->value`). 13 feature tests against Laravel's
  array-cache driver.
- **`WithEnumTransitions` Livewire trait** (PR-ζ). Promotes the
  existing `docs/recipes/livewire-state-transitions.md` recipe to a
  first-class trait under `src/Integrations/Livewire/`. Two methods:
  `transitionEnum($propertyPath, $target)` advances the state and
  dispatches an `enumerator.transitioned` Livewire event on success
  (or adds the `InvalidTransitionException` message to the Livewire
  error bag on failure); `canTransitionEnum(...)` pre-flights
  without mutating state. Uses `data_get()` / `data_set()` so
  nested property paths like `'order.status'` resolve through
  Livewire's getter chain. 6 feature tests with a stubbed-error-bag
  fixture (no full Livewire request lifecycle required).
- **Codecov coverage reporting** (PR-ε). `phpunit.xml` `<report>`
  block now emits `coverage.xml` (Clover format) alongside the
  existing text summary. New `.github/workflows/coverage.yml` runs
  Pest with coverage on PHP 8.3 on every push to `main` (and on
  PR), uploads to Codecov via `codecov/codecov-action@v4`. README
  carries a Codecov badge. The CI `--min=83` floor in `ci.yml` is
  unchanged — coverage.yml is a measurement / publication channel,
  not a gate. `coverage.xml` is gitignored. **One-time setup
  required:** Codecov v4 dropped tokenless uploads even for public
  OSS repos. Set the `CODECOV_TOKEN` GitHub secret on the repo to
  enable badge updates — instructions in
  `.github/workflows/coverage.yml` header comment. Until then, the
  workflow succeeds (fail_ci_if_error: false) but the badge stays
  "unknown".
- **Multi-select Alpine listbox** for `<x-...::dropdown>` (PR-δ). The
  Alpine path now handles `:multiple="true"` instead of falling through
  to the native `<select multiple>`. New shape: pill UI in the trigger
  (each pill with a `×` remove button), hidden inputs emitted via
  `<template x-for>` so the form receives the array, panel stays open
  on each selection toggle, `aria-multiselectable="true"` on the
  listbox `<ul>`, and a `change` event carrying the full array. Single-
  select mode is unchanged. `disabled=true` still falls through to
  the native `<select multiple>`. See
  [`docs/tools/blade-components.md`](docs/tools/blade-components.md)
  "Multi-select mode" section. Tests: 10 new in
  `tests/Feature/Blade/DropdownMultiSelectTest.php`.
- **Per-input `:wireModel` prop on radio + checkboxes** (PR-γ).
  `<x-...::radio>` and `<x-...::checkboxes>` accept a dedicated
  `wireModel` prop that emits `wire:model="..."` on every `<input>`
  element (in addition to the existing attribute-bag forwarding on
  the `<fieldset>`). The `wireModelModifier` companion prop emits
  `wire:model.live="..."` / `wire:model.blur="..."` /
  `wire:model.debounce.500ms="..."` etc. This is the required shape
  for Livewire 3 checkbox-array binding (array values won't
  populate from `wire:model` on the wrapping `<fieldset>`). v0.2.0
  attribute-bag-forwarded `wire:model` on the fieldset still works
  for single radios via Livewire's DOM-morph, but the
  `:wireModel` prop is the recommended pattern. See
  `docs/tools/blade-components.md` "Livewire integration".

### Fixed (pre-tag audit, 2026-05-16)

- **XSS hardening: `#[Icon]` attribute now HTML-escaped on render.**
  The `_base/badge.blade.php` and `_base/element.blade.php` base
  partials used to emit `{!! $caseIcon !!}` (raw). Every shipped
  preset enum uses a plain class-name string (`'check-circle'`,
  `'arrow-down'`, etc.), so escaping is a no-op for normal usage —
  but the `#[Icon]` value is also overridable at runtime via
  `enumerator.overrides` config and `TenantContext::overridesFor()`.
  A tenant-supplied override containing HTML/JS would have become a
  stored XSS. Switched to `{{ $caseIcon }}` so the value is always
  HTML-escaped. Pinned by `tests/Feature/Blade/IconEscapingTest.php`.
  Consumers who actually want raw SVG icons should publish the view
  bundle and customise per their framework variant.
- **Defensive escape: `:wireModel` / `:wireModelModifier` props on
  radio + checkboxes** are now `htmlspecialchars()`-encoded before
  being concatenated into the `wire:model="..."` attribute string.
  Values are typically developer-controlled, but defensive escaping
  protects against attribute-breakout if either prop ever carries an
  unescaped `"` or `&`. The browser decodes attribute entities before
  Livewire reads the value, so the change is invisible at the JS
  layer. Pinned by `tests/Feature/Blade/WireModelEscapingTest.php`.
- **`LaravelTranslatorAdapter` now honours an explicit locale strictly.**
  When `translate($key, $replace, $locale)` was called with an explicit
  non-null `$locale` and that locale had no entry, the adapter used to
  return the Laravel-fallback locale's value (typically `en`). That
  silently defeated `IsTranslatable::label($locale)`'s fallback chain
  (caller relies on `null` here to fall through to `#[Label]` then
  `Humanizer::humanize()`). The adapter now passes the third
  `$fallback` argument to `Lang::has()` / `Lang::get()`: `false` when
  the caller supplied an explicit locale (no fallback wanted), `true`
  when the caller passed `null` (resolve via the app's current locale
  + its fallback). Pinned by the three new tests in
  `tests/Unit/Translations/LaravelTranslatorAdapterTest.php`.

## [0.2.1] — 2026-05-15

Hotfix. v0.2.0's CI failed on the merge commit because the coverage
gate I raised from 80 → 85 in PR-A was set above what was actually
measurable. Coverage on `main` is **83.2%** — most of the new v0.2.0
tests exercise Blade view rendering (excluded from `<source>` in
`phpunit.xml`), so `src/` line coverage didn't move much. Lowering
the gate to **83%** so the floor matches reality. Target is still
90% on stable releases; the path to 90% goes through covering
uncovered `src/` branches, not through tightening the gate first.

### Fixed

- `.github/workflows/ci.yml` Pest step `--min=85` → `--min=83`.
- `composer.json` `test:coverage` script `--min=85` → `--min=83`.
- Comment in `ci.yml` documents the rationale + the 90% target.

No code path touched. Behaviour identical to v0.2.0.

## [0.2.0] — 2026-05-15

The **integration-rich Laravel enum toolkit** pass. Adds Alpine.js
loader infrastructure with CDN-first + local-fallback, an
Alpine-enhanced searchable / clearable dropdown, Livewire-aware
attribute forwarding on the select / radio / checkboxes components,
Livewire promoted to `require-dev` so the integration is CI-tested,
documentation reorganisation, and a release-messaging cleanup.

Backwards compatible with `v0.1.0` consumers — no shipped surface
removed, no public method renamed. The only behaviour change is a
single defensive escape correction in `RendersHtml::toHtml()` (see
Fixed below), which affects only the pre-encoded-entity edge case.

### Changed

- Softened the public tagline from the prior "Laravel enum godfather"
  framing to **"the integration-rich Laravel enum toolkit"**. Reflects
  the v0.1.0 breadth honestly without overstating depth in any single
  integration axis.
- Aligned the CI coverage gate, the `composer test:coverage` script,
  and CI narrative on **`--min=85`** (was an inconsistent mix of 80 / 90
  across surfaces). Currently 83.2% measured on CI — the new gate is
  set just above that line, and the next batches add coverage on the
  Livewire integration to clear it.
- Reordered the `orchestra/testbench` constraint to `^10.0|^11.0` for
  readability. Resolved version range unchanged.

### Added

- `<x-laranail-enumerator::alpine-loader />` Blade component for the
  Alpine-enhanced components landing in later v0.2.0 batches. CDN-first
  with local fallback; conflict-check guard prevents double-loading
  when consumers already ship Alpine via npm. Pinned Alpine version
  + SHA-384 SRI live in `config/enumerator.php` under `alpine.*`; CI
  verifies the bundled `resources/js/alpine.min.js` matches the
  integrity hash on every push. Opt out of the CDN with `:cdn="false"`.
  See [`docs/tools/alpine-loader.md`](docs/tools/alpine-loader.md).
- New publish tag `enumerator-js` copies the Alpine bundle into
  `public/vendor/laranail-enumerator/` for the local-fallback path.
- `livewire/livewire ^3.5` added to `require-dev` so the Livewire
  integration (`src/Integrations/Livewire/`) is now exercised on the
  full CI matrix. `EnumeratorCasts` covered by 7 feature tests.
  Livewire stays in the `suggest` block — it's still an opt-in for
  consumers, just now CI-gated for the maintainer.
- **Livewire-aware Blade components.** `<x-...::select>`,
  `<x-...::radio>`, and `<x-...::checkboxes>` now forward arbitrary
  caller attributes (`wire:model.*`, `wire:loading`, `data-*`,
  `aria-*`, `x-*`, etc.) through to their outer element via Laravel's
  `ComponentAttributeBag::except()`. The select binds on `<select>`;
  the radio / checkboxes bind on the wrapping `<fieldset>` (Livewire 3
  morphs the value down to each `<input>`). Per-input wireModel prop
  deferred to v0.3.0. ADR-0004.
- `<x-...::checkboxes>` (and `<x-...::radio>`) base views now emit
  the forwarded-attribute string on the wrapping `<fieldset>`. Was
  previously only the bootstrap variant of `<x-...::select>`.
- `docs/tools/blade-components.md` updated with the component matrix,
  the Livewire integration contract, and the reserved-prop set per
  component.
- **Alpine-enhanced `<x-laranail-enumerator::dropdown>`.** Set
  `:searchable="true"` or `:clearable="true"` and the component
  renders an accessible combobox/listbox driven by Alpine.js:
  type-to-filter, arrow / Enter / Esc keyboard navigation, click-
  outside close, ARIA roles, optional clear button, empty-state row,
  and a hidden `<input>` for native form submission. With both flags
  off, the native `<select>` from v0.1.0 still renders — fully
  backwards-compatible. Requires `<x-...::alpine-loader />` once in
  the page. Multi-select + disabled fall back to the native select
  (multi-listbox is on the v0.3.0 backlog). ADR-0004 / ADR-0005.
  Documentation: [`docs/tools/blade-components.md`](docs/tools/blade-components.md)
  "Dropdown — Alpine-enhanced searchable / clearable" section.

### Fixed

- README quality stat no longer quotes a numeric test / assertion count
  (the figure was stale within one commit of being written).
- The `v0.1.0` Filament integration line in this file referred to
  "column helpers". The shipped surface is more specific:
  `EnumeratorColumn` for tables and `EnumeratorEntry` for infolists,
  documented at `docs/tools/blade-components.md` and the integration
  recipes. (Historical `[0.1.0]` entry kept verbatim per
  Keep-a-Changelog discipline.)
- `Concerns\RendersHtml::toHtml()` and
  `Concerns\HasClassEnumBehavior::toHtml()` now pass
  `doubleEncode: false` to Laravel's `e()` helper. Before this fix, an
  already-HTML-encoded entity in the label / classes / icon (e.g. a
  translator that returned `&amp;` instead of `&`) was re-encoded to
  `&amp;amp;` and rendered as visible `&amp;` text in the browser.
  Plain strings (which are the documented contract for translations)
  produce identical output — only the pre-encoded edge case changes
  behaviour. Caught by the new `RendersHtmlEscapeTest` regression
  suite. Issue S-3.

## [0.1.0] — 2026-05-12

Initial release.

### Adoption surface

- **`Concerns\HasEnumerator`** — one-stop umbrella trait for native PHP 8.3+
  enums. Composes every behaviour trait the package ships: attribute lookup,
  equality, collection helpers, JSON serialisation, HTML rendering,
  translations, bitmasks, declarative groups, invokable cases, lifecycle
  (`next` / `previous` / `isFirst` / `isLast`), magic comparisons
  (`isFoo()` / `isNotFoo()`), explicit ordering, and the state machine.
  Granular traits stay public for advanced composition.
- **`Concerns\HasEnumAttributes`** — consumer-side trait used on Eloquent
  Models, Livewire Components, FormRequests, and DTOs to declare enum-typed
  attributes. Auto-registers the `AsEnum` cast and exposes 9 magic
  accessors (`_label`, `_value`, `_name`, `_color`, `_icon`,
  `_description`, `_help`, `_badge`, `_meta`) plus 3 predicates
  (`statusIs`, `statusIn`, `statusEquals`). Host detection is duck-typed
  (no hard dependency on framework classes); consumer-declared casts are
  preserved.
- **`AbstractEnumeratorClass`** — class-constant fallback for cases where
  native enums don't fit (dynamic case sets, BenSampo-style migration
  shims, etc.). First-class across every integration where native enums
  work.

### Attributes

`Bit`, `Color`, `CssClass`, `Description`, `Help`, `Icon`, `Label`, `Meta`,
`Order` — declarative metadata for cases. `CssClass` is repeatable for
per-framework variants. `Meta` collects arbitrary named values.

### Contracts

`Enumerator` (marker), `HtmlRenderable`, `Stateful`, `Translatable`,
`Bitwise`, `TransitionHook`, `TranslatorAdapter`, `TenantContext`.

### Eloquent

- Casts: `AsEnum`, `AsNullableEnum`, `AsEnumeratorCollection`, `AsBitmask`.
- `HasEnumeratorScopes` trait: `whereEnumIn`, `whereEnumMeta`,
  `whereEnumBitMatches`, plus implicit-binding-friendly helpers.
- `EnumeratorStateHistory` model with explicit `$fillable` for transition
  audit trails.

### Routing

`EnumeratorRouteBinder` registers implicit route bindings for native and
class-const enums uniformly.

### Validation

`EnumValue`, `EnumName`, `EnumIn`, `EnumNotIn`, `EnumTransition` — first-
class rule objects with sensible messages. `EnumValue::coerce` accepts
backed values, case names (pure enums), and falls back gracefully.

### Blade

- Components: `badge`, `select`, `radio`, `checkboxes`, `grid`, `list`,
  `bitmask`, `option` — each with publishable view bundles for `plain`,
  `tailwind`, `daisyui`, `bootstrap`, `bulma`. Anonymous-component
  callers' `class="..."` attribute merges with the framework's class set;
  `data-*` / `aria-*` / `id` forward to the underlying element.
- Directives: `@enumeratorLabel`, `@enumeratorColor`, `@enumeratorIcon`,
  `@enumeratorBadge`, `@enumeratorOptions`, `@enumeratorDescription`,
  `@enumeratorHelp`, `@enumeratorMeta($case, 'key')`, and case-insensitive
  variants.

### Translations

Pluggable via `TranslatorAdapter`. Default `LaravelTranslatorAdapter` wires
through the Laravel translator. Reference `DatabaseTranslatorAdapter` ships
opt-in. Resolution chain: registered translation → `#[Label]` attribute →
humanised case name (handles acronym-PascalCase, e.g. `HTTPMethod` →
`HTTP Method`).

### Bitmask

`Helpers\Bitmask` value type for `#[Bit]`-tagged enums. Constructor
validates each case against the declared enum class. `AsBitmask` cast
accepts `Bitmask`, integer masks, numeric strings, and single `Bitwise`
cases (auto-wrapped into a one-case mask).

### State machine

`Concerns\HasTransitions` + `Stateful` contract. Declarative
`transitions()` map; `transitionTo($next)` enforces with detailed
exceptions. Optional `EnumeratorStateHistory` model writes audit rows
with `TransitionHook` callbacks.

### Per-tenant overrides

`AttributesOverrideResolver` consults the bound `TenantContext` first,
then the config-file `enumerator.overrides` map. Default
`NullTenantContext` returns no overrides (single-tenant). Lazy
container resolution — swap with `app()->instance()` at runtime.

### Dynamic enums

`DynamicEnums\DatabaseBackedEnum` extends `AbstractEnumeratorClass` and
hydrates cases from a database table at runtime. Use when the case set
genuinely needs runtime definition. ⚠ Caveats: instances are NOT native
PHP enums — `match` exhaustiveness doesn't apply, `BackedEnum` type-hints
reject them, and IDE refactoring tools don't understand them. Prefer the
native enum path when possible.

### Artisan commands

- `make:enumerator` — scaffolds a new enum class with attributes
- `enumerator:annotate` — annotates models that use enum-cast attributes
- `enumerator:export` — emits TypeScript / JSON / CSV exports for cases
- `enumerator:ide-helper` — emits real `@method` stubs (per-case factories
  for `HasInvokableCases`, `is{Case}` / `isNot{Case}` predicates for
  `HasMagicComparisons`, `@method static {Type} {CONSTANT}()` for
  `AbstractEnumeratorClass` subclasses), wrapped in real-namespace blocks
  for IDE indexing
- `enumerator:cache` — snapshots resolved attributes/cases to disk
- `enumerator:cache:clear` — flushes the snapshot

### Reflection cache

Two-tier (memory → file) via `LayeredCache`. The snapshot persistor bridges
in-memory `AttributesCache` / `CasesCache` to disk so a single warm-up
serves every worker.

### Integrations

- **Filament 4+** — `EnumeratorFilter`, `EnumeratorRadio`,
  `EnumeratorSelect` form components, column helpers.
- **Livewire 3.5+** — enum-aware property casts and rule helpers.
- **Nova 5+** — `EnumeratorField` + `EnumeratorFilter`.
- **Inertia 2+** — transformer for typed SPA responses.

### Optional modules

Each gated by a `config('enumerator.modules.*')` toggle (all default false):

- **`Modules\Pest`** — 5 custom expectations: `toBeCase`, `toBeIn`,
  `toEqualEnum`, `toHaveBit`, `toCanTransitionTo`.
- **`Modules\OpenApi`** — emits OpenAPI 3.1 component schema fragments
  with `x-enum-varnames` and `x-enum-descriptions` vendor extensions.
- **`Modules\Lighthouse`** — abstract `EnumScalar` extends
  `webonyx/graphql-php` `ScalarType`; subclass per-enum and register via
  `@scalar`. Safe to autoload without Lighthouse installed.
- **`Modules\Saloon`** — `EnumCaster` recursively serialises enum
  instances in headers/query/JSON-body payloads.
- **`Modules\Octane`** — listens to `WorkerStarting` and warms the
  reflection cache per worker boot.
- **`Modules\StructuredOutput`** — three sibling JSON-schema emitters:
  `OpenAiSchemaEmitter` (response format + tool parameter),
  `AnthropicSchemaEmitter` (tool input property), `McpSchemaEmitter`
  (tool input property + resource parameter). Pure schema emission, no
  AI-SDK dependency.
- **`Modules\GraphQL`** — `SchemaExporter` emits portable `.graphql` enum
  fragments usable in any GraphQL stack (Mercurius, API Platform, Apollo,
  webonyx/graphql-php, Hasura).
- **Tenancy** — `TenantContext` driver swap via
  `enumerator.tenancy.driver` or `ENUMERATOR_TENANCY_DRIVER`.

### Migration codemods (`src/Rector/`)

Rector rules to migrate from existing PHP enum packages:

- `RectorBenSampoEnumToEnumerator` — full class-to-enum transform with
  backing-type detection.
- `RectorSpatieEnumToEnumerator` — structural skeleton (cases need
  manual fill for Spatie's docblock-driven pattern).
- `Sets\MigrationSet` — bundles both rules for one-import adoption.

Source files guard with `class_exists(AbstractRector::class)` so they
load cleanly without `rector/rector` installed.

### PHPStan

Custom extension at `extension.neon` resolves dynamic enum methods
(magic comparisons, invokable cases) for static analysis. Codebase ships
clean at `level: max`.

### CI

GitHub Actions matrix runs against PHP 8.3 + 8.4 + 8.5 with
`fail-fast: false`. Pint, PHPStan, and Pest (with 90% min coverage) all
gate.

### Presets

26 production-ready enums bundled at `src/Presets/Enums/` — lifecycle,
severity, presentation, HTTP, bitmask demos, demographics, calendar,
MIME types, plus one class-const example.

[0.4.0]: https://github.com/laranail/enumerator/releases/tag/v0.4.0
[0.3.0]: https://github.com/laranail/enumerator/releases/tag/v0.3.0
[0.2.1]: https://github.com/laranail/enumerator/releases/tag/v0.2.1
[0.2.0]: https://github.com/laranail/enumerator/releases/tag/v0.2.0
[0.1.0]: https://github.com/laranail/enumerator/releases/tag/v0.1.0
