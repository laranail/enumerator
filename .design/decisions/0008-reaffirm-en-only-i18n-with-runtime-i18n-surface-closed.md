# ADR-0008 — reaffirm en-only translations, close runtime i18n gap

- **Status:** Accepted
- **Date:** 2026-05-16
- **Deciders:** Imani Manyara
- **Supersedes / amends:** [ADR-0003](0003-translations-en-only-plus-community-scaffolding.md) (extends, doesn't replace)

## Context

ADR-0003 (dated 2026-05-15) committed v0.2.0 to shipping `en` only
plus a community-translation contribution recipe at
[`docs/recipes/contributing-translations.md`](../../docs/recipes/contributing-translations.md).

A re-evaluation was scoped into v0.4.0 as PR-ρ — "reassess: does the
package have community demand justifying locale PRs?"

Datapoints as of 2026-05-16:

- **v0.1.0** tagged + on origin.
- **v0.2.0 + v0.2.1** tagged + on origin (5 months in the wild).
- **v0.3.0** prep-complete but **not yet tagged** (33 commits past
  `v0.2.1` on local `main`). Not published on Packagist; no
  composer-require consumers possible.
- **Zero community translation PRs** received against the existing
  `lang/en/enumerator.php` (the scaffolding has been live since
  v0.2.0).
- **Zero issues / discussions** asking for additional locales.
- **No marketing surface** — OSS portal pages on
  `opensource.simtabi.com` still pending (manual user step).

Conclusion: there is no observable community demand. The default
should remain `en` only.

## The runtime i18n gap that DID surface

Auditing the v0.3.0 surface for PR-ρ uncovered a real defect: the
multi-select Alpine dropdown (shipped in v0.3.0 PR-δ) embedded **nine
hardcoded English strings** in the Blade view + Alpine `x-data`
state:

- `"Search…"` (search input placeholder)
- `"Search options"` (search input `aria-label`)
- `"No matches."` (empty-state row)
- `"Clear selection"` (clear button `aria-label`)
- `"Remove " + entry.label` (pill remove button `aria-label`)
- `"Added " / "Removed " / "Selected " / "Selection cleared"` (PR-ο
  announcement strings)

These strings never reached the translator, so a consumer who shipped
`lang/vendor/enumerator/{locale}/enumerator.php` still got English
in the multi-select UI. That contradicts both ADR-0003's stated
posture (en is the *default*, not the *only*) and the runtime
`TranslatorAdapter` plumbing.

## Decision

**Two-part:**

1. **Reaffirm en-only ship by default.** No second-locale shipping in
   v0.4.0. The community scaffolding stays as-is. The conditions
   under which this flips are written down below; the next session
   doesn't have to re-derive them.

2. **Close the runtime i18n gap.** Route every user-facing dropdown
   string through the Laravel translator namespace
   `enumerator::enumerator.components.dropdown.*`, with English
   defaults in `lang/en/enumerator.php`. The
   `Blade\Components\Dropdown::resolveDropdownStrings()` helper
   handles the Alpine-state-vs-static-attribute split: announcement
   patterns (`Added :label`) are broken into a JS-prefix + the
   `:label` placeholder so Alpine can substitute the option label
   at runtime; non-pattern strings flow through directly. Locale
   tests pin the en defaults AND verify that
   `Translator::addLines(..., 'fr', 'enumerator')` overrides reach
   every string.

## Conditions under which en-only flips

The next time PR-ρ is scoped, flip to **shipping a second locale**
(probably `es` or `pt_BR`, the two most-asked-for in adjacent
Laravel package landscapes) if **any one** of these triggers fires:

- Packagist downloads cross **1,000 installs / month**.
- The repo accepts **two community translation PRs** under the
  existing scaffolding without maintainer rewrites.
- A first-party consumer (Mukoracms, Botble integration, etc.) ships
  a `lang/vendor/enumerator/` overlay in their own repo — promote
  it upstream as the seed.

Otherwise: stay en-only and revisit each major release.

## Consequences

- **v0.4.0 includes the runtime gap closure.** New keys land under
  `components.dropdown.*` in `lang/en/enumerator.php`. No breaking
  change — the new keys are purely additive; the prior key set
  (`validation`, `components.{select,radio,grid}`, `aria`,
  `commands`) is untouched.
- **The `docs/recipes/contributing-translations.md` recipe is now
  accurate to the v0.4.0 key set.** Recipe updated in this PR.
- **No second-locale ship.** Community contribution path remains
  the only path to non-English. Documented in the recipe and the
  README.

## Alternatives considered

- **Ship a second locale alongside en in v0.4.0.** Would multiply
  maintenance overhead before any consumer-level pull exists, and
  risk stale translations as v0.4.0 → v0.5.0 churns the surface
  (Lighthouse / Saloon / a11y additions all add strings). Rejected
  pending the flip conditions above.
- **Wait on closing the runtime gap until a consumer reports it.**
  The dropdown shipped in v0.3.0 has been pre-tag the whole time —
  no consumer has hit it yet. But closing the gap NOW means v0.3.0
  ships with the right plumbing and the next session doesn't owe a
  v0.3.x hotfix. Rejected.
- **Auto-detect locale from `app()->getLocale()` instead of routing
  through the translator.** Would require a second per-locale
  mapping table inside the package. The translator namespace
  pattern is the Laravel-idiomatic shape; reusing it is no extra
  cost.

## References

- [ADR-0003](0003-translations-en-only-plus-community-scaffolding.md)
- [`docs/recipes/contributing-translations.md`](../../docs/recipes/contributing-translations.md)
- [`lang/en/enumerator.php`](../../lang/en/enumerator.php) — canonical key set
- [`src/Blade/Components/Dropdown.php`](../../src/Blade/Components/Dropdown.php) — `resolveDropdownStrings()`
