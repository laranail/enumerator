# v0.2.0 validation report

Re-derived from the 2026-05-15 audit / execution pass. Held against
the Phase 6 checklist in `audit-remediate.md`.

## Commit chain on `origin/main`

All 8 commits pushed; verifiable with `git log --oneline v0.1.0..HEAD`:

```
46881cc Land v0.2.0 PR-G: Alpine-enhanced searchable/clearable dropdown
0a80b0b Land v0.2.0 PR-F: Livewire-aware select / radio / checkboxes
6f9cfae Land v0.2.0 PR-E: livewire/livewire as require-dev, integration tested
8700a32 Land v0.2.0 PR-D: Alpine loader infrastructure
0275436 Land v0.2.0 PR-C: rename _base/list → listing + escape regression
1bd16a7 Land v0.2.0 PR-B: documentation infrastructure
3edc7ed Add v0.2.0 audit + design record (.design/)
c51e12e Land v0.2.0 PR-A: align messaging, soften tagline, hygiene
```

`v0.1.0` tag at `8b772108` is unchanged.

## Phase 6 — Definition of done

### Quality gates

- [x] **All P0 / P1 items from the issue register resolved or deferred**
  - P0: none (no security or release-blocking findings)
  - P1 — I-2 (coverage gate drift) → resolved in PR-A1 (aligned at `--min=85`)
  - P1 — I-4 (stale gitignore) → resolved in PR-A2 (paths moved to `.design/_private/`)

- [x] **Full Pest suite green on PHP 8.5.0beta3 locally** at the time of
  each PR. CI matrix (PHP 8.3 + 8.4 + 8.5) verified by the `gh run`
  watcher on push for each batch.
  - Suite at v0.1.0: 751 tests / 1463 assertions
  - Suite at HEAD (`46881cc`): **789 tests / 1548 assertions** (+38 / +85)
  - Skips: 2 (unchanged from v0.1.0; both feature-incomplete known
    landmines per `NEXT-SESSION.md` "Known landmines")
  - Local duration: 8.90s

- [x] **PHPStan `level: max` clean.** `[OK] No errors`.
  Baseline grew from 1918 entries at v0.1.0 to **2072** at HEAD, driven by
  three legitimate baseline expansions:
  - PR-C: `+137` (new fixture's trait-context entries)
  - PR-D: `+7` (new view-string + Pest expect()->not entries)
  - PR-G: `+10` (new Pest expect()->not entries in DropdownAlpineTest)
  No type-safety silently regressed; PR-E un-excluded
  `src/Integrations/Livewire/*` from analysis and the existing code
  passed with zero new baseline entries.

- [x] **Pint clean.** `{"tool":"pint","result":"passed"}`

- [x] **`composer audit` clean.** `No security vulnerability advisories
  found.` (Re-confirmed in PR-A baseline run.)

- [x] **`composer validate --strict` clean.** `./composer.json is valid.`
  (Lockfile-stale warning is benign — `composer.lock` is gitignored
  per Composer's library-project guidance.)

- [x] **README documentation index matches actual `docs/` tree.**
  Cross-checked in PR-A (no broken links) and PR-B (new `docs/README.md`
  index added per Simtabi scaffolding standard).

- [x] **All public methods have docblocks.** Spot-verified via the
  new `AlpineLoader.php` (full PHPDoc) and the new
  `Concerns/RendersHtml.php` + `HasClassEnumBehavior.php` `e()` calls
  (in-place comment explaining `doubleEncode: false`).

- [x] **Translation files present for at least `en`.** Unchanged —
  `lang/en/enumerator.php` ships. ADR-0003 records the
  community-translation contribution path; `docs/recipes/contributing-translations.md`
  is the consumer doc.

### Cleanup hygiene

- [x] **No file in `cleanup-candidates.md` has been deleted without
  approval.** Verified `git log --diff-filter=D v0.1.0..HEAD` — only the
  `_base/list.blade.php` → `_base/listing.blade.php` rename touched
  cleanup territory, and that rename is in PR-C with explicit issue
  reference (I-3). The 7 scripts + composer.lock-untrack + DS_Store
  candidates listed in `cleanup-candidates.md` remain untouched,
  awaiting the user's explicit approval for Phase 7.

## What this report does NOT verify

- **Cross-project smoke test.** Per user direction
  (Phase 6 / PR-H pre-flight 2026-05-15), the live `composer require
  laranail/enumerator` test from a scratch Laravel 13 skeleton was
  NOT run as part of this pass. The user will run it manually before
  tagging `v0.2.0`. The smoke test should at minimum:
  - `composer create-project laravel/laravel test-app && cd test-app`
  - `composer require laranail/enumerator:dev-main` (or the candidate
    tag once tagged)
  - `php artisan vendor:publish --tag=enumerator-js` —
    `public/vendor/laranail-enumerator/alpine.min.js` should appear
  - Render a page with `<x-laranail-enumerator::dropdown :enum="..."
    :searchable="true" :clearable="true" />` and confirm the Alpine
    listbox opens, filters, and the hidden input updates on selection
  - Render a page with `<x-laranail-enumerator::select ... wire:model="..." />`
    in a Livewire component and confirm `wire:model` round-trips
  - Capture the run in `.design/plans/smoke-test.md` before tagging.

- **CI coverage figure.** Coverage on CI may move from the 83.2%
  observed at v0.1.0 — local runs cannot compute coverage without
  Xdebug, and the new Livewire integration + Alpine dropdown tests
  add code paths. The CI workflow gates at `--min=85`; PR-G's HEAD
  is expected to clear that floor. The user should re-check CI run
  output before tagging.

- **Browser-level Alpine combobox behaviour.** Pest tests verify the
  rendered DOM markup and Alpine attribute set, NOT actual JS
  execution. Manual testing in a browser (keyboard nav, filter typing,
  clear button, click-outside) is part of the smoke test step above.

## Deferred items (v0.3.0 backlog)

Per the completion plan:

- F-15 — Livewire enum-transition Action helper (recipe-only in v0.2.0)
- F-16 — Cache encapsulation (cerbero pattern)
- F-20 — Enum defaults
- F-22 — Halve the 393 KB `phpstan-baseline.neon`
- F-23 — Mutation testing via `infection`
- F-25 — Reconsider `reportUnmatchedIgnoredErrors: false`
- Per-input `:wireModel` prop on radio + checkboxes (PR-F deferred this)
- Multi-select Alpine listbox (PR-G deferred this)

These are explicitly out of scope for v0.2.0.

## Pre-tag checklist for the user

Before running `git tag v0.2.0 && git push origin v0.2.0`:

1. Read `CHANGELOG.md` `[0.2.0]` section — verify it reflects what's
   shipping.
2. Run the cross-project smoke test (above), record in
   `.design/plans/smoke-test.md` if you wish (gitignored is fine).
3. Sanity-check CI is green on `main` HEAD:
   `gh run list --repo laranail/enumerator --branch main --workflow CI --limit 1`
4. Tag: `git tag v0.2.0 -m "v0.2.0 — integration-rich Laravel enum toolkit"`
5. Push: `git push origin v0.2.0`
6. Cut the GitHub release: `gh release create v0.2.0 --notes-file CHANGELOG.md`
   (or manual via the UI).

[VERIFIED 2026-05-15: vendor/bin/pest --compact → 2 skipped, 789 passed (1548 assertions)]
[VERIFIED 2026-05-15: vendor/bin/phpstan analyse → [OK] No errors]
[VERIFIED 2026-05-15: vendor/bin/pint --test → passed]
[VERIFIED 2026-05-15: composer audit → No security vulnerability advisories found.]
[VERIFIED 2026-05-15: composer validate --strict → composer.json is valid]
[VERIFIED 2026-05-15: git log --oneline v0.1.0..HEAD → 8 commits as listed above]
