# Feature gap analysis — v0.2.0 candidates

Inputs: Phase 1 issue register, Phase 2 landscape survey, the two
pre-flight commitments made today (en + community translation
scaffolding; Blade + Livewire + Alpine frontend), and Tusente /
Botble reference docs.

For each candidate: source → decision (**Adopt / Skip / Defer**) →
rationale + cost.

Bias is toward **Skip** unless the feature is both (a) absent today
and (b) clearly aligned with the package philosophy. "Godfather"
framing must not turn into surface-level feature dumping.

## Must-have for v0.2.0 (must-fix from Phase 1 P1s)

| # | Item | Source | Decision | Effort | Risk | Notes |
|---|---|---|---|---|---|---|
| F-01 | Reconcile coverage gate across CI / composer.json / CHANGELOG / README | I-2 | **Adopt** | S | low | Pick `--min=85` (currently passing); update all four surfaces in one commit |
| F-02 | Fix stale .gitignore (`/plans/` → `/.design/_private/` or `.design/plans/{NEXT-SESSION,audit-remediate}.md`) | I-4 | **Adopt** | S | low | Decide one of the two patterns; document in PR body |

## Should-have for v0.2.0

| # | Item | Source | Decision | Effort | Risk | Notes |
|---|---|---|---|---|---|---|
| F-03 | Add `docs/README.md` index | I-5 | **Adopt** | S | low | Simtabi scaffolding standard. Mirror root README "Documentation" section, paths resolved from `docs/` |
| F-04 | Add `[Unreleased]` CHANGELOG block | I-10 | **Adopt** | S | low | Keep-a-Changelog discipline |
| F-05 | Refresh README test/assertion count (or move to a generic phrase) | I-1 | **Adopt** | S | low | Either auto-generate from CI or drop the number |
| F-06 | Blade view naming drift fix (`_base/list.blade.php` → `listing.blade.php`) | I-3 | **Adopt** | S | low | Single rename + RoutesToFrameworkView resolution-map update |
| F-07 | `scripts/README.md` documenting one-time scaffolds | I-11 | **Adopt** | S | low | Light archival doc; no deletion |
| F-08 | Comment on `static-analysis.yml` rationale | I-12 | **Adopt** | S | low | One-line top-of-file comment |
| F-09 | Document env var surface in `docs/configuration.md` | S-6 | **Adopt** | S | low | Already half-documented in config/enumerator.php comments |
| F-10 | Architecture test: `extraAttrs` is internally-constructed only | S-2 | **Adopt** | S | low | Single Pest arch test against `Select.php` |
| F-11 | Doc note: `#[Icon]` / overrides are trusted input | S-1 | **Adopt** | S | low | One paragraph in `docs/tools/attributes.md` + `TenantContext` PHPDoc |

## v0.2.0 differentiators (user-authorised in Phase 2 pre-flight)

| # | Item | Source | Decision | Effort | Risk | Notes |
|---|---|---|---|---|---|---|
| F-12 | Livewire-aware `<x-...::select>` component (wire:model.live, validation slot) | landscape diff | **Adopt** | M | med | No other Laravel enum package does this. Differentiator. Needs Livewire 3.5+ dev dep + Pest tests via Testbench's Livewire harness |
| F-13 | Livewire-aware `<x-...::radio>` and `<x-...::checkboxes>` | landscape diff | **Adopt** | M | med | Same surface; reuse the F-12 plumbing |
| F-14 | Alpine.js enhancement: searchable / clearable `<x-...::dropdown>` | landscape diff | **Adopt** | M | med | Alpine plugin file shipped via `vendor:publish --tag=enumerator-js`; no NPM dep, no build step |
| F-15 | Livewire enum-transition Action helper | landscape diff | **Defer** | L | med | "Trigger a state transition from a Livewire action" — useful but easy to ship as a recipe in `docs/recipes/livewire-state-transitions.md` first; promote to first-class in v0.3.0 |

## Cross-pollination from published packages

| # | Item | Source | Decision | Effort | Risk | Notes |
|---|---|---|---|---|---|---|
| F-16 | Cache encapsulation (cerbero pattern) | cerbero/laravel-enum | **Defer** | M | low | Useful but niche. The cache facade is already trivial to use directly. Ship as recipe in v0.2.0, possibly promote to module in v0.3.0 |
| F-17 | Session encapsulation (cerbero pattern) | cerbero/laravel-enum | **Skip** | — | — | Smaller niche than cache; session keys are usually feature-scoped, not enum-scoped |
| F-18 | Faker provider (`$faker->status()`) | spatie/laravel-enum | **Adopt as recipe** | S | low | Document in `docs/recipes/factories.md`. First-class provider class is overkill given how trivial it is |
| F-19 | `henzeb/enumhancer` macros / property handling | henzeb/enumhancer | **Skip** | — | — | AGPL; cannot lift code. Conceptually adjacent to our `#[Meta]` attribute. No new surface needed |
| F-20 | `henzeb/enumhancer` defaults | henzeb/enumhancer | **Defer** | S | low | "A case is the default for absent input" — already covered by `AsNullableEnum` semantics. Revisit if user requests demand it |
| F-21 | `archtechx/enums` `Options` trait shape | archtechx/enums | **Already covered** | — | — | `HasEnumerator::options($placeholder)` exists |

## Items NOT to revisit

These are baked decisions from the prior 13 sessions. Revisiting them
is OUT OF SCOPE for v0.2.0:

- D34/D54 — `HasEnumerator` umbrella **includes** `HasInvokableCases`.
- D35 — `HasEnumAttributes` uses `mergeCasts()`.
- D36 — `TranslatorAdapter` pluggable; default `LaravelTranslatorAdapter`.
- D37 — 8 optional modules, all config-gated, vendor-soft.
- D46 — Rector codemods with `class_exists()` guards.
- D48 — `Modules\GraphQL` framework-agnostic, distinct from
  `Modules\Lighthouse`.
- D49 — `DatabaseBackedEnum` extends `AbstractEnumeratorClass` (NOT
  native).
- D50 — `TenantContext` consulted BEFORE config overrides; lazy
  container resolution.

(Full register in `.design/plans/NEXT-SESSION.md`, "Decisions baked
into the codebase".)

## Code-smell remediations (defer to dedicated work)

| # | Item | Source | Decision | Effort | Risk | Notes |
|---|---|---|---|---|---|---|
| F-22 | Halve the 393 KB `phpstan-baseline.neon` | I-13 | **Defer to v0.3.0** | L | med | Worthwhile but long. Classify entries (a) real fixes (b) generic-template noise on CasesCollection (c) cross-version drift. Out of scope for v0.2.0 |
| F-23 | Mutation testing via `infection` | I-8 | **Defer to v0.3.0** | L | low | Coverage is already at 83.2%; mutation testing reveals which 17% is toothless. Park for now |
| F-24 | Codecov / Cobertura output | I-9 | **Defer** | S | low | Trigger only when the project wants a coverage badge |
| F-25 | Reconsider `reportUnmatchedIgnoredErrors: false` | S-7 | **Defer to v0.3.0** | M | med | Tied to F-22 — regenerating the baseline cleanly would let this guard come back |

## Cleanup candidates (Phase 7 — never autonomous)

These will get listed in `.design/plans/cleanup-candidates.md` when the
plan is approved:

- `scripts/refactor-facades.py` — one-time, last run during scaffolding
- `scripts/scaffold*.sh` (six files) — one-time generators
- (Possibly) consolidate the `.design/` plan files when the project
  moves to a stable v0.2.0 — but **NOT** the append-only blocks in
  `NEXT-SESSION.md`.

## Summary count

- **Adopt for v0.2.0:** 14 items (F-01..F-14)
- **Adopt as recipe:** 1 (F-18)
- **Defer to v0.3.0+:** 6 (F-15, F-16, F-20, F-22, F-23, F-25)
- **Skip:** 3 (F-17, F-19, F-24)
- **Already covered:** 1 (F-21)

Estimated effort for v0.2.0 adoption set: roughly **7-10 sessions of
the size of the prior coverage batches**. The three Livewire/Alpine
items (F-12, F-13, F-14) are the largest single bucket — they'll need
their own ADRs.

## Open questions for the Phase 3 plan

1. Should F-14 (Alpine) ship Alpine via CDN-friendly snippet in the
   docs, or bundle a `resources/js/enumerator-alpine.js` file that
   consumers `import` from? — flagged for Phase 3 question.
2. Does F-12/F-13 require Livewire as a **dev dependency** (so tests
   can run) or as a **suggest** only (so the integration is opt-in but
   not tested in CI)? Tests under Testbench's Livewire harness require
   the package to actually be installed during CI — flagged for
   Phase 3 question.
3. Should the "godfather" tagline be softened to "integration-rich
   Laravel enum toolkit" given the landscape findings? — flagged for
   Phase 3 question.
