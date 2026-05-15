# Issue register — laranail/enumerator

Findings from Phase 1 stock-take. Severity P0..P3; effort S/M/L (S ≤ 1h,
M ≤ ½ day, L > ½ day). Issue IDs are stable and will carry forward to
the completion plan in Phase 3.

Security findings live in `security-audit.md` and are not duplicated
here — they have a `S-*` prefix and are cross-referenced.

## Bugs

(none surfaced by audit — all gates green, no functional regressions
observed)

## Inconsistencies

### I-1 — Stale test/assertion counts in README
- **Severity:** P2 — public-facing
- **File:** `README.md:53`
- **Description:** README says "261 tests / 670 assertions on PHP
  8.3 / 8.4 / 8.5". Real counts are **751 tests / 1463 assertions**
  (`[VERIFIED 2026-05-15: vendor/bin/pest ...]`).
- **Suggested fix:** Replace the brittle exact-figure claim with a
  resilient phrasing ("comprehensive Pest suite, PHP 8.3 / 8.4 / 8.5
  matrix"). Updating the number again will recur on every backfill.
- **Effort:** S

### I-2 — Coverage minimum drift across three surfaces
- **Severity:** P1 — release messaging
- **Files:** `.github/workflows/ci.yml:48` (`--min=80`),
  `composer.json:94` (`"test:coverage": "pest --coverage --min=90"`),
  `CHANGELOG.md:190` ("with 90% min coverage"), `README.md` does not
  state a number but the CHANGELOG and composer script disagree with CI.
- **Description:** The binding gate is `--min=80`; everything else
  advertises `--min=90`. Either raise the gate or correct the advert.
- **Suggested fix:** Decide a number, set it in all four surfaces in
  one commit. The handoff doc's coverage arc table shows 83.2% in
  batch 13; `--min=85` would be honest and currently-passing.
- **Effort:** S

### I-3 — Blade view base/framework naming drift
- **Severity:** P2 — contributor footgun
- **Files:** `resources/views/components/_base/list.blade.php` (base
  name) vs. `resources/views/components/{bootstrap,bulma,daisyui,plain,tailwind}/listing.blade.php`
  (framework name).
- **Description:** Five framework dirs all use `listing.blade.php`; the
  base file is `list.blade.php`. The Blade component class is
  `Listing.php`. A contributor adding a new framework variant will copy
  from `_base/list.blade.php` and end up with a file that doesn't match
  the others.
- **Suggested fix:** Rename `_base/list.blade.php` → `_base/listing.blade.php`.
  Update `RoutesToFrameworkView`'s resolution map and any test that
  hardcodes the path. No public-API change.
- **Effort:** S

### I-4 — `.gitignore` points at the wrong plans path
- **Severity:** P1 — could leak local-only files
- **File:** `.gitignore:5` (`/plans/`)
- **Description:** Files moved from `plans/` → `.design/plans/`. The
  gitignore still excludes the old `/plans/` directory only. The
  current `.design/plans/NEXT-SESSION.md` and `audit-remediate.md` are
  no longer covered by gitignore — they will be staged on `git add .`.
- **Suggested fix:** Replace `/plans/` with `/.design/plans/`. If any
  `.design/plans/` artifacts SHOULD be committed (issue register,
  baseline doc, etc.), pin those exceptions via `!.design/plans/{file}.md`.
  Or: move the live handoff and audit-remediate template into a
  separate `.design/_private/` directory and gitignore that path
  specifically. Recommend the latter — it survives the addition of new
  artifacts without re-touching gitignore. The `/.design/references/`
  entry the audit just added is already correct.
- **Effort:** S

### I-5 — `docs/README.md` index missing
- **Severity:** P2 — Simtabi scaffolding standard violation
- **File:** `docs/` (absent)
- **Description:** Simtabi LLC scaffolding standard (`/opensource/CLAUDE.md`)
  explicitly lists `docs/README.md` as a required file (index linking
  to every other doc). The audit task brief lists it under the docs
  scaffolding. The root `README.md` carries that linkage today, but
  navigating into the `docs/` tree on GitHub shows a directory listing
  with no narrative.
- **Suggested fix:** Generate `docs/README.md` mirroring the linkage
  in the root README's "Documentation" section, but with relative paths
  that resolve from `docs/`.
- **Effort:** S

### I-6 — `composer.lock` is committed despite global rule
- **Severity:** P2 — global policy violation
- **File:** `composer.lock` (committed)
- **Description:** Global `~/.claude/CLAUDE.md` says `.gitignore` should
  include `composer.lock` for a library. The current `.gitignore:15`
  DOES list `composer.lock`, but `composer.lock` is still in the
  working tree (and presumably history). Either the gitignore line is
  doing nothing (because the file was committed before the rule was
  added), or the file is in a `git ls-files` whitelist.
- **Suggested fix:** Run `git rm --cached composer.lock` in the next
  commit so the gitignore rule actually takes effect. `composer.lock`
  for libraries is debated — Composer's own docs say keep it for
  apps, omit for libraries. Recommend omitting.
- **Effort:** S

### I-7 — `orchestra/testbench` constraint order is unusual
- **Severity:** P3 — cosmetic
- **File:** `composer.json:55`
- **Description:** `"orchestra/testbench": "^11.0|^10.0"` — usually
  the higher major comes first. Composer accepts it; readers do a
  double-take.
- **Suggested fix:** Reorder to `^10.0|^11.0` and standardise across
  all constraints for the same convention.
- **Effort:** S

## Gaps

### I-8 — No mutation testing configured
- **Severity:** P3 — quality maturity
- **Files:** (no `infection.json5` present)
- **Description:** Coverage is point-in-time. Mutation testing reveals
  which assertions are toothless. `infection` is a one-shot install,
  but tuning is L-effort.
- **Suggested fix:** Park as v0.3.0 work. For v0.2.0, do nothing.
- **Effort:** L

### I-9 — phpunit.xml emits text-summary coverage only
- **Severity:** P3 — CI integration
- **File:** `phpunit.xml:36`
- **Description:** Only `<report><text outputFile="php://stdout" .../>`
  is configured. Codecov and similar integrations expect Clover or
  Cobertura. Today CI uses `pest --coverage --min=80` which is
  self-contained, so this is only a problem if the project decides to
  publish a coverage badge or wire Codecov.
- **Suggested fix:** Add `<clover outputFile="coverage.xml" />` under
  the `<report>` block when the project decides to publish a badge.
  Until then, defer.
- **Effort:** S (when needed)

### I-10 — No CHANGELOG `[Unreleased]` section
- **Severity:** P2 — release hygiene
- **File:** `CHANGELOG.md` ends at the `[0.1.0]` block
- **Description:** Keep-a-Changelog standard has an `[Unreleased]`
  block at the top that accumulates entries as you go, so cutting a
  new release becomes a `s/[Unreleased]/[0.2.0]/` rename. The current
  CHANGELOG starts straight at `[0.1.0]`.
- **Suggested fix:** Add `## [Unreleased]` above `## [0.1.0]`. Add an
  `Added` / `Changed` / `Fixed` skeleton.
- **Effort:** S

### I-11 — `scripts/` is undocumented, mixed-purpose
- **Severity:** P3 — repo hygiene
- **Files:** `scripts/{refactor-facades.py,scaffold.sh,scaffold-docs.sh,scaffold-framework-variants.sh,scaffold-framework-views.sh,scaffold-presets.sh,scaffold-step-2.sh}`
- **Description:** Seven shell + python scaffolding scripts that
  appear to be one-time bootstraps. Not referenced in CI, README, or
  CONTRIBUTING. A new contributor doesn't know whether these are
  still part of the workflow or vestigial.
- **Suggested fix:** Either (a) add a `scripts/README.md` listing the
  one-time purpose of each and dating their last run, or (b) move
  them into `.design/scaffold-history/` (gitignored). Recommend (a) —
  the history is interesting and short. **Cleanup candidate; never
  delete autonomously.**
- **Effort:** S

### I-12 — `static-analysis.yml` duplication is intentional but undocumented
- **Severity:** P3 — CI shape
- **File:** `.github/workflows/static-analysis.yml`
- **Description:** `static-analysis.yml` runs only PHPStan, only on
  PHP 8.3, with `--error-format=github`. That `--error-format=github`
  is the value-add: it emits inline GitHub annotations on PR diffs,
  which the in-matrix PHPStan step in `ci.yml` (which uses default
  format) does not. Not a duplicate — a deliberate fast-feedback
  channel for PR reviewers.
- **Suggested fix:** Add a top-of-file comment explaining the rationale
  so the next contributor doesn't think it's a duplicate to delete.
- **Effort:** S

## Lost functionality

(none surfaced — the package shipped v0.1.0 with everything the
handoff documents, and the 13 coverage batches did NOT delete public
API. `HasEnumeratorBehavior` is now a wrapper around `BehaviorCore`
but the wrapping is byte-identical per the handoff's R1 risk register.)

## Code smells worth fixing

### I-13 — PHPStan baseline is 393 KB
- **Severity:** P2 — analyser noise
- **File:** `phpstan-baseline.neon`
- **Description:** `reportUnmatchedIgnoredErrors: false` silently
  permits drift. The baseline at 393 KB is a sign that level: max
  has caught real signal AND lots of generic-template noise. Hard to
  tell which.
- **Suggested fix:** As a v0.2.0 task, classify baseline entries:
  (a) genuine to-fix items; (b) generic-template noise that needs
  better `@template` annotations on `CasesCollection`; (c)
  cross-version drift entries. Migrate (a) into actual fixes; tune
  (b) at the class level; accept (c). Aim to halve the baseline.
- **Effort:** L

### I-14 — Filament integration `EnumeratorColumn` claim in CHANGELOG
- **Severity:** P3 — wording
- **File:** `CHANGELOG.md:136`
- **Description:** CHANGELOG line 136 says "Filament 4+ —
  EnumeratorFilter, EnumeratorRadio, EnumeratorSelect form components,
  column helpers." The actual file is `EnumeratorColumn`, not
  "column helpers" plural. Cosmetic.
- **Suggested fix:** Rename "column helpers" → "`EnumeratorColumn` for
  tables, `EnumeratorEntry` for infolists."
- **Effort:** S

### I-15 — README documentation claim parity
- **Severity:** P3 — accuracy
- **File:** `README.md` lines 588-632 (the Documentation section)
- **Description:** README enumerates `docs/tools/cases-collection.md`
  and `docs/recipes/api-resources.md` etc. All present, all resolve.
  But the README "Getting started" subsection lists
  `docs/getting-started.md` while there's no link to `docs/README.md`
  (which doesn't exist anyway — see I-5).
- **Suggested fix:** Rolled into I-5.
- **Effort:** (covered by I-5)

## Cross-cutting / Phase-3 inputs

These aren't issues per se — they're inputs the completion plan needs.

- **Translation locales:** Currently `lang/en` only. The Botble
  reference ships ~40 locales. Decide v0.2.0 scope: `en`-only (with
  scaffolding for community translation) is the lowest-risk choice
  given the small surface area today.
- **Frontend scope:** Blade components only (no JS today). Confirm
  this is the v0.2.0 plan; if Livewire/Alpine integration is desired,
  it deserves an ADR.
- **Reference codebase A (Botble):** copied into
  `.design/references/reference-a-botble/`. Phase-2 research can mine
  its `core/base/src/Enums/`, `EnumCastable.php`, multi-locale
  translation tree, and `EnumColumn.php` for adoption candidates.
- **Reference codebase B (Tusente):** copied into
  `.design/references/reference-b-tusente/`. Has rich docs
  (`COMPARISON.md`, `MIGRATION.md`, etc.) that may show patterns the
  current code already covers — or might not.

## Severity rollup

| Severity | Count | IDs |
|---|---|---|
| P0 | 0 | — |
| P1 | 2 | I-2, I-4 |
| P2 | 6 | I-1, I-3, I-5, I-6, I-10, I-13 |
| P3 | 7 | I-7, I-8, I-9, I-11, I-12, I-14, I-15 |
| Security P0-P1 | 0 | (see security-audit.md) |
| Security P2-P3 | 7 | S-1 … S-7 |

Two **P1**s carry through to Phase 3 as must-have for v0.2.0; the rest
are negotiable.

[VERIFIED 2026-05-15: README test count claim at README.md:53]
[VERIFIED 2026-05-15: ci.yml gate at .github/workflows/ci.yml:48]
[VERIFIED 2026-05-15: composer test:coverage script at composer.json:94]
[VERIFIED 2026-05-15: CHANGELOG 90% claim at CHANGELOG.md:190]
[VERIFIED 2026-05-15: .gitignore /plans/ entry at .gitignore:5]
[VERIFIED 2026-05-15: docs/README.md → ls error (file absent)]
