# v0.3.0 validation report

Re-derived from the 2026-05-15 v0.3.0 cycle. Held against the
Phase 6 checklist in `audit-remediate.md`. Analog of
`validation-report.md` (the v0.2.0 report).

## Commit chain on `origin/main`

18 commits pushed since `v0.2.1`. Verifiable with
`git log --oneline v0.2.1..HEAD`:

```
c312aa0 docs(CHANGELOG): backfill PR-ζ, PR-η, infection entries
cfb67ac PR-ε sub-batch: install infection + non-blocking mutation workflow
b162bd6 PR-η: IsCacheKey trait + Cacheable contract
56ef98e PR-ζ: WithEnumTransitions Livewire trait
87745e9 docs(ci): document Codecov token requirement after PR-ε run
aa35ff8 PR-ε: Codecov coverage reporting (Clover + workflow + badge)
38ff526 PR-δ: multi-select Alpine listbox for <x-...::dropdown>
723b48a PR-γ: per-input :wireModel on radio + checkboxes
cef6936 PR-α-resid-2: fixture @method annotations + HasTransitions path-scope
5d16bcd docs(design): document PR-α-resid-1 dead-end (Pest typing)
24bb08d docs(design): v0.3.0-scope — PR-α landed (5 batches), residue plan
adb45b5 PR-α Batch 5: path-scoped method.nonObject for service providers
2852580 PR-α Batch 4: path-scoped suppression for trait-template cascades
97b8156 PR-α Batch 3: narrow mixed-to-string casts in 3 trait-cascading sites
7c295a4 PR-α Batch 2: annotate iterable types, constrain HasTransitions
741dd69 PR-α Batch 1: drop redundant @var, narrow trait $this via @phpstan-var
5580857 docs(design): reconcile v0.3.0-scope.md with audit measurements
8370020 docs(design): seed v0.3.0 scope doc
```

`v0.2.0` tag at `410d990` and `v0.2.1` tag at `6185e14` are
unchanged. v0.3.0 is **staged but NOT tagged** — that's the next
human action.

## Quality gates

- [x] **Pest suite green on PHP 8.5.0beta3 locally and on the
  PHP 8.3 / 8.4 / 8.5 CI matrix.**
  - At v0.2.1 (HEAD `6185e14`): 789 tests / 1548 assertions /
    2 skipped.
  - At v0.3.0 HEAD (`c312aa0`): **825 tests / 1627 assertions /
    2 skipped** (+36 tests, +79 assertions).
  - Local duration: 11.69s.
  - Skips: 2 (unchanged from v0.1.0 / v0.2.0; both feature-incomplete
    known landmines per `.design/_private/NEXT-SESSION.md`).

- [x] **PHPStan `level: max` clean.** `[OK] No errors`.
  - Baseline: **724 unique patterns / 1400 total occurrences /
    213 KB** at HEAD.
  - At v0.2.1: 689 patterns / 1343 occ / 211 KB. Net delta over
    the v0.3.0 cycle: +35 patterns / +57 occurrences / +2 KB,
    accounted for by PR-γ + PR-δ + PR-ζ + PR-η + infection test
    additions (each new test file added a small batch of
    Pest-typing baseline entries; same pattern documented in
    PR-α-resid-1 dead-end note).
  - vs the start of PR-α (HEAD `8370020`, 1181 patterns / 2072
    occurrences / 413 KB): **−39% patterns / −32% occurrences /
    −48% file size** cumulatively after baseline shrink + the new
    v0.3.0 surface.

- [x] **Pint clean.** `{"tool":"pint","result":"passed"}`.

- [x] **`composer audit` clean.** `No security vulnerability
  advisories found.`

- [x] **`composer validate --strict` clean.**
  `./composer.json is valid`. composer.lock-stale warning is benign
  (lock is gitignored per library-project guidance from v0.2.0).

- [x] **CI workflows green.** ci.yml + static-analysis.yml +
  coverage.yml all green on `main` HEAD verified via
  `gh run list`. infection.yml is new in this cycle and
  intentionally non-blocking on first runs.

- [x] **All public methods have docblocks.** Spot-verified via the
  three new public surfaces:
  - `Concerns/IsCacheKey` — full docblock per method with TTL
    semantics + usage example.
  - `Integrations/Livewire/WithEnumTransitions` — full docblock per
    method with Blade-side recipe.
  - `Contracts/Cacheable` — marker contract with docblock.

## Surface added in v0.3.0

| Item | Where | Tests |
|---|---|---:|
| PR-γ — per-input `:wireModel` on Radio / Checkboxes | `src/Blade/Components/Radio.php` + `Checkboxes.php` + base views | 8 |
| PR-δ — multi-select Alpine listbox | `_base/dropdown.blade.php` (extended x-data) | 10 |
| PR-ε — Codecov + Clover output | `phpunit.xml` + `.github/workflows/coverage.yml` + README badge | 0 (CI plumbing) |
| PR-ζ — `WithEnumTransitions` Livewire trait | `src/Integrations/Livewire/WithEnumTransitions.php` | 6 |
| PR-η — `IsCacheKey` trait + `Cacheable` contract | `src/Concerns/IsCacheKey.php` + `src/Contracts/Cacheable.php` | 12 |
| Infection mutation testing infrastructure | `infection.json5` + `.github/workflows/infection.yml` + composer.json | 0 (CI plumbing) |
| PR-α (baseline shrink across 5 batches + resid-2) | 30+ files touched; phpstan-baseline.neon halved | 0 (refactor) |

**Total v0.3.0 test delta: +36 tests / +79 assertions.**

All v0.3.0 surface additions are NEW code — no v0.2.x public API
was removed or renamed. Backwards-compatible per ADR-0001's `^0.x`
discipline.

## Coverage

CI coverage as last observed (2026-05-15, `aa35ff8`):
**83.3 %** measured on the PHP 8.3 job, above the `--min=83` gate.
HEAD (`c312aa0`) has not been re-measured on CI yet; the next push
to main will trigger coverage.yml and refresh the figure. Codecov
upload still requires the `CODECOV_TOKEN` secret (manual external
step — see `NEXT-SESSION.md`).

## What this report does NOT verify

- **Browser-level Alpine combobox behaviour** for the new
  multi-select dropdown (PR-δ). Same caveat as v0.2.0: Pest tests
  verify the rendered DOM markup and Alpine attribute set, not
  actual JS execution. Manual smoke test in a browser before
  tagging — exercise the pill UI, the click-to-remove, the
  panel-stays-open behaviour on multi-select.

- **Livewire round-trip** for `WithEnumTransitions` (PR-ζ). The
  trait's behaviour was verified with a stubbed-error-bag fixture
  (no full Livewire request lifecycle). Manual smoke test in a
  real Livewire 3 component before tagging.

- **Cache driver matrix** for `IsCacheKey` (PR-η). Tests run against
  Testbench's array driver only. Redis / Memcached / database
  drivers should work because the trait passes through Laravel's
  Cache facade unchanged, but the matrix isn't exercised in CI.

- **Infection baseline kill rate.** The infection workflow runs
  but `continue-on-error: true` keeps it from gating. First CI
  measurement will produce the baseline figure; setting the
  `minMsi` / `minCoveredMsi` gate is a v0.3.x task.

- **Cross-project smoke test.** Last run on 2026-05-15 against
  v0.2.0 HEAD; not re-run for v0.3.0. The new public surface
  (`WithEnumTransitions`, `IsCacheKey`, `Cacheable`) should be
  re-smoked from a scratch Laravel 13 project before tagging —
  same recipe as `.design/_private/smoke-test.md`.

## Deferred to v0.3.x / v0.4.0

Per the v0.3.0-scope.md residue:

- **PR-α-resid-1** (Pest expect() typing) — upstream-coordination
  task with the Pest larastan plugin. Two in-package experiments
  failed in 2026-05-15 session; documented in
  `.design/plans/v0.3.0-scope.md` "PR-α residue" section.
- **PR-α-resid-3** (trait `@mixin` audit) — reassessed as not
  viable without breaking heterogeneous-host trait composition.
- **PR-β** (coverage push to clear 85%) — measurement-first
  approach blocked locally by lack of xdebug; could run from
  CI's coverage.yml log to identify uncovered branches. Carried
  into v0.3.x.
- **PR-θ** (browser-level Alpine CI test) — out of scope; nice-
  to-have, requires Playwright/Cypress integration.
- **Infection floor** (`minMsi` / `minCoveredMsi`) — first
  measurement pending. Set the floor in v0.3.x after observing
  the baseline kill rate.
- **`IsSessionKey` trait** — analog of `IsCacheKey` for session
  keys. Smaller niche than cache; defer until a use case appears.

## Pre-tag checklist for the user

Before running `git tag v0.3.0 && git push origin v0.3.0`:

1. **Manual smoke test** — boot a scratch Laravel 13 project, install
   `laranail/enumerator:dev-main`, exercise:
   - `<x-...::dropdown :multiple="true" :searchable="true" :clearable="true">` —
     pill UI, click-to-remove, panel-stays-open, hidden inputs on
     form submit
   - `<x-...::radio wire-model="status" />` in a Livewire 3
     component — verify wire:model on each input
   - `<x-...::checkboxes wire-model="permissions" />` in a Livewire 3
     component — verify array binding actually populates
   - A Stateful enum with `WithEnumTransitions` — exercise a valid
     and an invalid transition; verify error bag + change event
   - `IsCacheKey` — round-trip put/get/forget against the file
     driver and (if available) Redis

2. **Codecov token** — if not already configured, set it (per
   `.github/workflows/coverage.yml` SETUP REQUIRED comment) so the
   v0.3.0 release page shows the badge correctly.

3. **Sanity-check CI:**
   `gh run list --repo laranail/enumerator --branch main --limit 5`
   — confirm all green.

4. **Tag and release:**

   ```bash
   git tag v0.3.0 -m "v0.3.0 — Livewire + cache + multi-select + mutation testing"
   git push origin v0.3.0
   gh release create v0.3.0  # release.yml auto-creates; gh release edit
                             # afterwards with the [0.3.0] CHANGELOG block
   ```

[VERIFIED 2026-05-15: vendor/bin/pest → 2 skipped, 825 passed (1627 assertions)]
[VERIFIED 2026-05-15: vendor/bin/phpstan analyse → [OK] No errors]
[VERIFIED 2026-05-15: vendor/bin/pint --test → passed]
[VERIFIED 2026-05-15: composer audit → No security vulnerability advisories found.]
[VERIFIED 2026-05-15: composer validate --strict → composer.json is valid]
[VERIFIED 2026-05-15: git log --oneline v0.2.1..HEAD → 18 commits as listed above]
