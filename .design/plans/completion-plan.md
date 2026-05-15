# v0.2.0 completion plan — laranail/enumerator

Synthesised from Phase 1 stock-take (`session-recap.md`,
`codebase-inventory.md`, `issue-register.md`, `baseline.md`,
`security-audit.md`), Phase 2 research (`.design/research/landscape.md`,
`feature-gap.md`), the 12 design principles (`.design/principles.md`),
and the 7 ADRs (`.design/decisions/0001..0007`).

**Status: AWAITING EXPLICIT APPROVAL before Phase 4 execution.**

---

## What we have, working

From Phase 1 baseline (all gates green 2026-05-15):

- v0.1.0 shipped public at GitHub tag `v0.1.0` → commit `8b772108`.
- `main` head: `e6fb99d` (14 commits past tag).
- **Tests:** 751 pass / 2 skip / 1463 assertions, 9.85s.
- **Static:** PHPStan `level: max` — `[OK] No errors`.
- **Style:** Pint clean.
- **Audit:** `composer audit` — no advisories.
- **Validate:** `composer validate --strict` — valid.
- **Coverage:** 83.2% (CI-authoritative, last 2026-05-14).
- Every feature column in the 2026-05-15 landscape matrix is covered.
- 21 baked decisions D34..D54 verified intact by spot-check.

Not modifying:

- Public API surface visible in `composer.json::autoload.psr-4`.
- D34..D54 (the 21 baked decisions).
- The PHPStan baseline (deferred to v0.3.0 per ADR-0001).

## What we're keeping, fixing

Phase 1 issue register findings grouped by area. Issue IDs from
`issue-register.md`:

### Release messaging (must-have)
- **I-2** — Reconcile coverage gate across CI / composer.json / CHANGELOG.
- **I-4** — Fix stale `.gitignore` (`/plans/` → `.design/` mapping).
- **I-1** — Stale test/assertion count in README (auto-derive or drop).
- **I-10** — Add `[Unreleased]` CHANGELOG block.
- **ADR-0007** — Soften tagline in README + composer description.

### Documentation (should-have)
- **I-5** — Add `docs/README.md` index (Simtabi scaffolding).
- **I-14** — Fix Filament "column helpers" wording in CHANGELOG.
- **S-1** — Doc note on `#[Icon]` / `TenantContext` trusted-input contract.
- **S-6** — Document env var surface in `docs/configuration.md`.

### Hygiene (should-have)
- **I-3** — Rename `_base/list.blade.php` → `listing.blade.php`.
- **I-6** — `git rm --cached composer.lock` so the gitignore rule activates.
- **I-7** — Reorder `orchestra/testbench` constraint to `^10.0|^11.0`.
- **I-11** — Document `scripts/` purpose (`scripts/README.md`).
- **I-12** — Comment on `static-analysis.yml` rationale.

### Test infrastructure (should-have)
- **S-2** — Architecture test: `extraAttrs` is internally-constructed only.
- **S-3** — Regression test: `IsTranslatable` doesn't double-render.

## What we're adding

ADR-driven new surface for v0.2.0:

### Differentiator stack (must-have for v0.2.0)
- **ADR-0005 + F-14** — `<x-laranail-enumerator::alpine-loader>` Blade
  component with CDN-first / local-fallback / conflict-detection /
  no-double-load behaviour.
- **ADR-0004 + F-12** — Livewire-aware `<x-...::select>`
  (`wire:model.live` + validation slot + `wire:loading` state).
- **ADR-0004 + F-13** — Livewire-aware `<x-...::radio>` +
  `<x-...::checkboxes>`.
- **ADR-0004 + F-14** — Alpine-enhanced searchable / clearable
  `<x-...::dropdown>`.

### Supporting changes (must-have)
- **ADR-0006** — `livewire/livewire: ^3.5` joins `require-dev`;
  `src/Integrations/Livewire/*` leaves `phpstan.neon` exclusion list;
  new Pest feature tests under
  `tests/Feature/Integrations/Livewire/`.
- **ADR-0005** — `resources/js/alpine.min.js` ships with pinned
  version + SRI hash in `config/enumerator.php`; new
  `enumerator-js` publish tag; new `docs/tools/alpine-loader.md`.
- **F-27 (new)** — `docs/comparison.md` with honest matrix vs.
  surveyed landscape (replaces "godfather" claim with evidence).

### Recipes (should-have)
- **F-26 (new)** — `docs/recipes/contributing-translations.md`
  (ADR-0003 — the community translation path).
- **F-18** — `docs/recipes/factories.md` covering the
  `$faker->status()` pattern (spatie-equivalent without shipping a
  provider).
- **F-15** — `docs/recipes/livewire-state-transitions.md` (defers
  the first-class state-transition Action to v0.3.0).

## What we're NOT adding (rationale)

- **F-16/F-17** — Cache / session encapsulation (cerbero pattern). Useful
  but niche; the Laravel facades work directly today. Revisit v0.3.0.
- **F-19** — `enumhancer` macros (AGPL — cannot lift code; the
  conceptually-adjacent `#[Meta]` attribute already covers it).
- **F-20** — Enum "defaults" (already covered by `AsNullableEnum`).
- **F-22..F-25** — Mutation testing, Codecov, baseline trim,
  `reportUnmatchedIgnoredErrors: true` — all deferred to v0.3.0.

## What we're removing (proposed)

**Nothing autonomous.** All proposed deletions live in
`cleanup-candidates.md` for explicit approval:

- `scripts/scaffold*.sh` and `scripts/refactor-facades.py` —
  candidates for archival to `.design/scaffold-history/` rather than
  deletion. Stage in `cleanup-candidates.md`.

## Ordered work items

Each row is a single PR / atomic commit set. Acceptance criteria are
"all CI gates green AND new tests cover the change." Effort scale:
S ≤ 1h, M ≤ ½ day, L > ½ day.

### PR-A — Quick hygiene + release messaging (must-have, P1/P2)

Bundle the trivial fixes into one commit so v0.2.0's first batch is a
solid foundation.

| # | Change | Issue | Effort | Acceptance |
|---|---|---|---|---|
| A1 | Reconcile coverage gate at `--min=85` across `ci.yml`, `composer.json` script, `CHANGELOG.md` `[Unreleased]` block | I-2 | S | All four surfaces show 85; CI re-runs green at 85 (currently 83.2% — see note) |
| A2 | Fix `.gitignore`: `/plans/` → `/.design/_private/`; move `NEXT-SESSION.md` + `audit-remediate.md` into `.design/_private/` | I-4 | S | `git status` after move shows no untracked private files |
| A3 | Drop / generalise README test-count claim | I-1 | S | README no longer carries a numeric test count |
| A4 | Add `## [Unreleased]` block to CHANGELOG with `Changed` / `Added` skeleton | I-10 | S | CHANGELOG lints clean against Keep-a-Changelog |
| A5 | Soften tagline in README hero + composer.json description (ADR-0007) | F-27 prelude | S | New tagline reads: "the integration-rich Laravel enum toolkit." |
| A6 | Fix CHANGELOG "column helpers" wording | I-14 | S | CHANGELOG names `EnumeratorColumn` + `EnumeratorEntry` explicitly |
| A7 | Reorder testbench constraint to `^10.0|^11.0` | I-7 | S | `composer validate --strict` still passes |
| A8 | `git rm --cached composer.lock` so existing `.gitignore` line takes effect | I-6 | S | `composer.lock` no longer tracked |
| A9 | Comment header on `static-analysis.yml` explaining its purpose | I-12 | S | File top comment documents the `--error-format=github` rationale |
| A10 | `scripts/README.md` describing one-time scaffolds | I-11 | S | New file lists each script + last-known purpose |

> **Note for A1**: 85% picks the smallest "currently passing" round
> number above 80%. If the new Livewire integration tests pull
> coverage below 85% transiently in PR-D/E, revisit before merge.

### PR-B — Documentation infrastructure (must-have, P2)

| # | Change | Issue | Effort | Acceptance |
|---|---|---|---|---|
| B1 | Add `docs/README.md` index mirroring root README's "Documentation" section | I-5 | S | Every link resolves; CI markdown-link check passes |
| B2 | Add `docs/comparison.md` from the landscape matrix | F-27 | M | Honest table vs. spatie / bensampo / archtechx / henzeb / cerbero; links to each |
| B3 | Document env var surface in `docs/configuration.md` | S-6 | S | Every `env(...)` call in `config/enumerator.php` documented |
| B4 | Doc note on `#[Icon]` / `TenantContext` trusted-input contract | S-1 | S | Paragraph in `docs/tools/attributes.md` + PHPDoc on `TenantContext` |
| B5 | `docs/recipes/contributing-translations.md` | F-26 | S | Step-by-step for adding a locale |
| B6 | `docs/recipes/factories.md` (faker pattern for enums) | F-18 | S | Snippet shows `Factory::definition` returning random case |

### PR-C — Hygiene + naming drift fix (should-have, P2)

| # | Change | Issue | Effort | Acceptance |
|---|---|---|---|---|
| C1 | Rename `_base/list.blade.php` → `_base/listing.blade.php`; update `RoutesToFrameworkView`'s resolution map; update any test paths | I-3 | S | All component tests pass; no test references the old path |
| C2 | Architecture test: `extraAttrs` is internally-constructed only | S-2 | S | Pest arch test rejects any caller passing `extraAttrs` from request input |
| C3 | Regression test: `IsTranslatable` doesn't double-render HTML | S-3 | S | Test asserts `e()` applied to translated string in `RendersHtml::toHtml()` flow |

### PR-D — Alpine loader infrastructure (must-have, P1)

ADR-0005. Lands BEFORE the dropdown enhancement so D and E build on it.

| # | Change | ADR / F | Effort | Acceptance |
|---|---|---|---|---|
| D1 | New `src/Blade/Components/AlpineLoader.php` + `_base/alpine-loader.blade.php` + framework variants if needed | ADR-0005 / F-14 | M | Component renders inline `<script>` with CDN URL from config |
| D2 | New `resources/js/alpine.min.js` (pinned version, sourced from CDN at build) | ADR-0005 | S | File present; SHA-384 SRI matches `config('enumerator.alpine.integrity')` |
| D3 | Add `'alpine' => ['version' => '3.x.x', 'integrity' => '...']` to `config/enumerator.php` | ADR-0005 | S | Config publishes; default values present |
| D4 | New publish tag `enumerator-js` in `EnumeratorServiceProvider::bootPublishing()` | ADR-0005 | S | `vendor:publish --tag=enumerator-js` copies the file |
| D5 | New `docs/tools/alpine-loader.md` documenting usage, CSP, opt-out flag | ADR-0005 | S | All linked from `docs/README.md` (PR-B) |
| D6 | Feature test: component emits CDN URL by default; `cdn=false` prop skips the CDN attempt | ADR-0005 | S | Pest feature test in `tests/Feature/Blade/AlpineLoaderTest.php` |
| D7 | CI workflow step: re-download pinned Alpine version + verify SRI matches | ADR-0005 | M | New CI job step fails the build on SRI mismatch (catches stale bundle) |

### PR-E — Livewire as require-dev + integration tests (must-have, P1)

ADR-0006. Lands before PR-F so the new components have a Livewire
test harness ready.

| # | Change | ADR | Effort | Acceptance |
|---|---|---|---|---|
| E1 | Add `livewire/livewire: ^3.5` to `require-dev` in composer.json; `composer update livewire/livewire` | ADR-0006 | S | `composer install` in CI fresh; Livewire's package discovery picks up |
| E2 | Remove `src/Integrations/Livewire/*` from `phpstan.neon` `excludePaths` | ADR-0006 | S | `phpstan analyse` still passes (possibly with a fresh baseline entry — accept or fix) |
| E3 | Pest feature tests for existing `EnumeratorCasts.php` | ADR-0006 / F-12 | M | At least 4 tests exercise the property cast contract |
| E4 | Suite-level: confirm coverage holds at or above the PR-A gate | ADR-0006 | S | CI green |

### PR-F — Livewire-aware components (must-have, P1)

ADR-0004. The headline v0.2.0 deliverable.

| # | Change | ADR / F | Effort | Acceptance |
|---|---|---|---|---|
| F1 | `<x-...::select>` accepts `wire:model.live`, integrates with Livewire validation, respects `wire:loading` | F-12 | M | Pest feature test wires a Livewire component and observes a select round-trip |
| F2 | `<x-...::radio>` same surface | F-13 | M | Same test pattern |
| F3 | `<x-...::checkboxes>` same surface | F-13 | M | Same test pattern |
| F4 | Update `docs/tools/blade-components.md` with Livewire usage snippets | F-12/13 | S | Snippets verified against the running tests |
| F5 | CHANGELOG `[Unreleased]` `Added` entries for each | release | S | Entries reference the docs |

### PR-G — Alpine-enhanced dropdown (must-have, P1)

ADR-0004 + ADR-0005.

| # | Change | ADR / F | Effort | Acceptance |
|---|---|---|---|---|
| G1 | Enhance `<x-...::dropdown>` with Alpine: searchable, clearable, keyboard-nav | F-14 | M | Manual smoke test confirms `arrow-down` / `Enter` / type-to-filter works; Pest tests cover rendered Alpine directives |
| G2 | Update `docs/tools/blade-components.md` dropdown section | F-14 | S | Snippet shows alpine-loader + dropdown together |
| G3 | Architecture test: dropdown must require the alpine-loader (lint, not runtime) | F-14 | S | Pest arch test or docblock with @uses |
| G4 | CHANGELOG entry | release | S | — |

### PR-H — Release prep + smoke test (must-have, P0)

The "ship v0.2.0" PR.

| # | Change | Effort | Acceptance |
|---|---|---|---|
| H1 | Run gates locally + on CI matrix, capture results in `.design/plans/validation-report.md` | S | Document attached to PR description |
| H2 | Fresh `composer require laranail/enumerator` from a scratch Laravel 13 app; smoke test all new components; record in `.design/plans/smoke-test.md` | M | Both new components render; no fatal errors |
| H3 | Tag `v0.2.0` ONLY after the user types "tag v0.2.0" | S | Tag points at the merge commit of PR-H |
| H4 | CHANGELOG: rename `[Unreleased]` → `[0.2.0] — YYYY-MM-DD` | S | Date is the tag day |

## Coverage / quality posture

- PR-A1 raises the CI gate to `--min=85`. PR-D + PR-E + PR-F + PR-G all
  add tests on the way in, so coverage should hold or improve.
- PHPStan baseline left as-is per ADR-0001 (deferred to v0.3.0).
- Pint clean before each PR merges.

## Risks

| ID | Risk | Mitigation |
|---|---|---|
| R-1 | Livewire's package version churn breaks tests mid-PR-E | Pin to `^3.5` (locked); Dependabot bumps go through review |
| R-2 | Alpine CDN unreachable from CI runners | Default to `cdn=false` in tests so CI doesn't depend on jsDelivr |
| R-3 | A1 coverage gate raise fails an as-yet-unwritten test | Gate raise lands in PR-A only after PR-D/E/F/G land if coverage drops |
| R-4 | PHPStan baseline new entries from PR-E2 surface real bugs | Fix the bugs OR add to baseline with a comment explaining why |

## Approval contract

- I will NOT execute any work item above until the user types one of:
  "approve plan", "go ahead", "ship it", or "execute Phase 4".
- Cleanup candidates (`cleanup-candidates.md`) require separate
  approval before any file is deleted.
- I will NOT push to GitHub or cut tags without an explicit instruction
  in the relevant PR's lifecycle.

## Effort summary

- PR-A: 10 trivial items (S each) — ~3-4 hours.
- PR-B: 6 doc items (mostly S) — ~½ day.
- PR-C: 3 hygiene items (S each) — ~2 hours.
- PR-D: 7 items, one M, six S — ~½ day.
- PR-E: 4 items, one M, three S — ~½ day.
- PR-F: 5 items, three M, two S — ~1 day.
- PR-G: 4 items, one M, three S — ~½ day.
- PR-H: 4 items, one M, three S — ~½ day.

**Aggregate: ~4-5 sessions of effort** (similar magnitude to the
13-batch coverage arc that landed v0.1.0).
