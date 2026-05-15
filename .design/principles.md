# Design principles — laranail/enumerator

Distilled from the v0.1.0 codebase, the D34..D54 baked decisions, the
2026-05-15 ecosystem survey, and the user's Phase-2 pre-flight
choices. These are the rules a contributor should be able to apply
when extending the package in a year's time.

## 1. Native enums first; class-const only as a fallback

PHP 8.1 introduced native enums and they are sufficient for ~95% of
cases. The package's primary surface — `HasEnumerator` and the
`Concerns/Core/BehaviorCore` traits — composes onto native
`BackedEnum` / `UnitEnum`.

`AbstractEnumeratorClass` exists only for the residual ~5%:
mixed-backing constants, runtime case definition, BenSampo-style
migration shims. **Use the native path unless you can't.** Document
the caveat (D49 — instances are NOT native enums; `match`
exhaustiveness, IDE refactor, `BackedEnum` typehints all break).

## 2. Composition over inheritance

The umbrella `HasEnumerator` trait is a one-line ergonomic; the
seven individual traits behind `BehaviorCore` are the real surface.
A consumer who needs only `HasMagicComparisons` should `use` only
that — not be forced to take the whole umbrella.

Every behaviour trait must be **independently usable**. Cross-trait
dependencies stay implicit and minimal; document them in the trait's
docblock if they exist.

## 3. Attributes-driven metadata; methods-driven behaviour

Static metadata (label, color, icon, description, bit position) is
declared as PHP attributes on cases:

```php
#[Label('Active'), Color('success'), Icon('check-circle')]
case Active = 'active';
```

Dynamic behaviour (transitions, validation, render flow) is
declared as methods on the enum or contracts implemented on the
enum. **Do not invent a new declaration shape** if attributes or
methods already do the job.

## 4. Pluggable, never coupled, when crossing a boundary

The package crosses three boundaries: the **translator**
(`TranslatorAdapter`), the **tenant context**
(`TenantContext`), and the **cache** (`LayeredCache` driver). Each
has a default (`LaravelTranslatorAdapter` / `NullTenantContext` /
`layered`), each is swappable via a single config key, and each is
**lazy-resolved from the container** so `app()->instance()` rebinding
works at test time (D36, D50).

When you add a new boundary in future versions, follow the same
pattern. Don't take a hard vendor dep.

## 5. Optional modules are config-gated, vendor-soft

The eight optional modules (Pest, OpenAPI, Lighthouse, Saloon,
Octane, StructuredOutput, GraphQL, Tenancy) follow D37:

- A boolean config toggle defaults to `false`.
- The module's `ServiceProvider` is registered unconditionally but
  no-ops when the toggle is off (or when its vendor marker class is
  absent).
- The module's source files MUST be autoloadable without the vendor
  library installed (use `class_exists()` guards on imports, see
  the Rector codemods D46 for the prototype).

When adding a new module: same pattern. No exceptions.

## 6. Laravel facades over raw functions, but not religiously

Reach for `Arr::`, `Str::`, `File::`, `Cache::`, `Lang::` when they
read more naturally than the raw `array_map` / `sprintf` / `trans`
form. Don't paper over a perfectly-readable `sprintf` with `Str::`
for the sake of it.

The rule that genuinely matters: **all queries go through Eloquent
bindings**. No `DB::raw` string interpolation, no hand-rolled SQL
with `$column = "WHERE x = '$user_input'"`. Phase 1 security audit
verified the package is clean on this front today (security-audit
S-* findings are P2/P3 defense-in-depth, none P0/P1).

## 7. Translations are first-class, not bolted on

Every user-facing string flows through `IsTranslatable` →
`TranslatorAdapter`. The default resolution chain is:

1. registered translation → 2. `#[Label]` attribute → 3. humanised
case name (handles acronym-PascalCase like `HTTPMethod` →
`HTTP Method`)

When you add a new component or directive, register a translation
key for any default string. Don't hardcode English.

v0.2.0 ships `en` only by design (user pref from 2026-05-15);
community contribution path is documented in
`docs/recipes/contributing-translations.md` (TBD).

## 8. Zero magic where a method call works

Magic comparisons (`$status->isActive()`) and invokable cases
(`Status::Active()`) **are** magic — they exist because they read
better, they're documented in the public API surface, and they have
dedicated tests. Adding new magic surfaces needs a higher bar:

- Document the resolution policy (case-sensitivity, ambiguity).
- Add an IDE-helper emission so consumers get autocomplete
  (`enumerator:ide-helper`).
- Add a PHPStan rule so static analysis doesn't error.

If you don't want to do all three, don't add the magic. Just ship
a method.

## 9. Backwards compatibility is a v1 contract

v0.1.0 is shipped. v0.2.0 within the `^0.x` major must not remove
or rename anything visible in `composer.json`'s `psr-4` autoload.
Breaking changes go through:

1. Deprecation in v0.x.y (with `@deprecated` PHPDoc + a notice
   logged once per process).
2. Removal in v1.0.0.

Adding new behaviour is fine. Renaming public methods is not.

## 10. Tests gate behaviour; static analysis gates types; coverage gates regression

Three gates, three distinct jobs:

- **Pest** asserts behavioural correctness. Public methods get unit
  tests; feature flows get feature tests. Architecture invariants
  (e.g. "all enums implementing `Enumerator` extend nothing but
  `UnitEnum`") get arch tests.
- **PHPStan level: max** asserts types. The baseline is
  acknowledged as imperfect (`reportUnmatchedIgnoredErrors: false`
  — issue I-13) but the GAUGE is still that new code adds zero
  baseline entries.
- **Coverage gate** prevents regression. The number itself is a
  side effect; the real value is the gate's existence. Pick a
  number that's currently passing and ratchet up only when a batch
  of intentional coverage work has landed.

## 11. The package is a library, not an app

No `App\\` namespace. No bootstrap files. No `php artisan` commands
that assume a host application's directory layout. Every
`vendor:publish` target uses the framework's `$this->app->path()`,
`$this->app->resourcePath()`, etc. — never a hardcoded `'app/Enums'`.

A consumer's app should be able to install the package, run the
test suite against a clean Testbench skeleton, and ship without ever
needing to "fork" anything.

## 12. The "godfather" claim has to be earned per release

The breadth of v0.1.0's feature surface is real (every column of the
2026-05-15 landscape matrix is covered). But **breadth alone is
fragile**. Each integration (Filament, Nova, Livewire, Inertia)
must remain green against its vendor's current major when that
vendor releases.

Phase 2 research's honest note: a tagline of
**"the integration-rich Laravel enum toolkit"** is more defensible
than "godfather of all enum packages." Either: tone it down, OR
back it with a real `docs/comparison.md` that says exactly which
packages we displace on which axis, and link it from the README.

Picked up as a Phase 3 open question.

---

## How to use this file

Add to it. Don't rewrite. Each principle is a load-bearing rule
that prior work has converged on; cite the relevant decision (D34..)
or issue (I-1..) so future readers can trace the reasoning.

If a principle proves wrong, leave it in place with a dated note:
`> **REVISED (YYYY-MM-DD):** principle N is amended to ...`.
The audit trail of how the design evolved is more useful than a
pristine-looking current snapshot.

[VERIFIED 2026-05-15: cross-checked against .design/plans/NEXT-SESSION.md decisions block]
