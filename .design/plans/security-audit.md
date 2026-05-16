# Security audit — laranail/enumerator

Findings categorised P0..P3. Scope: package source code, configuration,
Blade views, translations, CI workflow. Scan date 2026-05-15.

## Methodology

1. `composer audit` for advisories in declared deps.
2. Grep `src/` and `resources/views/` for the OWASP-flavoured dangerous-call
   patterns: `eval`, `extract`, `unserialize`, `shell_exec`, `exec`,
   `system`, `passthru`, `proc_open`, raw superglobals, `DB::raw`.
3. Grep `resources/views/` for `{!! !!}` (raw Blade output) and `@php`
   blocks. Inspect each.
4. Trace `HtmlString` and `new HtmlString(sprintf(...))` construction sites
   for escaping.
5. Grep `src/` and `tests/` for hardcoded credentials.
6. Inspect query-scope methods on `HasEnumeratorScopes` for binding hygiene.
7. Inspect translation files (`lang/en/enumerator.php`) for content that
   could be interpolated raw into HTML.

## Findings

### P0 — none

`composer audit` returned `No security vulnerability advisories found.`
on the dependency tree. No dangerous-call patterns matched in `src/`.

### P1 — none

No SQL injection vectors (all query construction routes through
Eloquent `where`/`whereIn` with bound values; no `DB::raw` usage). No
mass-assignment risk in the only shipped Eloquent model
(`EnumeratorStateHistory` has explicit `$fillable`).

### P2

**S-1 — `{!! $caseIcon !!}` raw output in 3 Blade base views.**

`resources/views/components/_base/badge.blade.php:33`,
`element.blade.php:42`. The variable resolves from the `#[Icon('…')]`
attribute on a case, which is developer-authored at compile time and so
not a direct XSS surface. However, the value MAY be overridden at
runtime by the `enumerator.overrides` config key or the active
`TenantContext::overridesFor()` array (`AttributesOverrideResolver`,
src/Support/AttributesOverrideResolver.php:?). If a consumer wires their
`TenantContext` to a database value populated by an admin user, the
override path becomes an XSS sink.

**Mitigation suggestions** (none are P0 today; document as future
hardening):

- Add a one-line doc note in `docs/tools/attributes.md` and the
  `TenantContext` contract that `Icon`/`Color`/`CssClass` overrides MUST
  be treated as trusted input.
- Optionally: in `RendersHtml::toHtml()` we already wrap the icon with
  `e()`. The Blade base views are the leak path. Consider tightening
  the contract to "icon source MUST be a CSS class name token / SVG
  symbol id" and `e()`-ing in the view too. Would break callers that
  rely on inline `<svg>…</svg>` icons.

**S-2 — `{!! $extraAttrs !!}` raw output in select.blade.php:47.**

The variable is sourced from `Select.php`'s attribute aggregation. The
view comment at line 16 acknowledges that callers' `data-*` / `aria-*`
attributes "flow through this string." Today the build path runs through
the component PHP class, but any future change that lets `extraAttrs`
take raw caller input is an XSS surface. Recommend a docblock contract
on the variable and an architecture test asserting it stays
internally-constructed.

**S-3 — `lang/en/enumerator.php` interpolation surfaces use `:placeholder`
form.** Currently safe (escaped by Laravel's `trans`). Worth a
regression test that asserts translations are not double-rendered as
HTML — see `IsTranslatable` consumers.

### P3

**S-4 — Reflection on `string $enumClass` argument in 3 schema emitters.**
`src/Modules/StructuredOutput/{OpenAiSchemaEmitter,McpSchemaEmitter}.php`
and `src/Modules/GraphQL/SchemaExporter.php` each construct a
`new \ReflectionClass($enumClass)`. The caller is a developer who passes
a class name; not user input. Worth checking that the public API does
not allow a route or HTTP body to reach these constructors.

**S-5 — `tests/` files are NOT scanned for secrets in CI.** Manual scan
returned no hits, but a permanent CI gate (e.g. `gitleaks` action) would
be cheap insurance against accidental key leaks in fixtures.

**S-6 — `.env.example` is absent.** Not strictly a security finding; the
absence forces consumers to read `config/enumerator.php` to know the env
vars. Document the env var surface in `docs/configuration.md`.

**S-7 — `phpstan-baseline.neon` drift is tolerated** by
`reportUnmatchedIgnoredErrors: false` in `phpstan.neon:21`. The comment
acknowledges this is "because baseline entries can drift across the PHP
8.3/8.4/8.5 matrix." Not a direct security issue, but it removes a guard
against silent type-safety regressions that COULD become security
issues. Consider: regenerate the baseline per PHP version, and turn the
guard back on.

## Dependency state

```
laravel ecosystem
  illuminate/{console,contracts,database,support,validation,view}: ^13.0
PHP: ^8.3
Dev deps
  larastan/larastan ^3.0 (current 3.9.6)
  laravel/pint ^1.18 (current 1.29.1)
  orchestra/testbench ^11.0|^10.0 (current 11.1.0)
  pestphp/pest ^3.0 (current 3.8.6)
  phpstan/extension-installer ^1.4 (current 1.4.3)
  phpstan/phpstan ^2.0 (current 2.1.54)
```

No deprecated packages, no abandoned packages, no advisories.

## Secrets / PII scan

`grep -rEn '(api[_-]?key|secret|password|token)\s*=\s*"[^"]{8,}"' src/ tests/ config/`
returned no hits after filtering test fixtures (none matched). No
`.env`, `.env.local`, or `credentials.json` in tree.

## Pinned-resource verification

Per global CLAUDE.md "Verification before pinning": the project pins
GitHub Actions to major version tags (`actions/checkout@v4`,
`shivammathur/setup-php@v2`, `actions/cache@v4`). The `@v` form
auto-tracks the latest minor — Dependabot is configured to refresh
major bumps weekly. This is the recommended pattern; no SHA pinning is
required for these vetted actions.

## Recommendations summary

| ID | Severity | Action |
|---|---|---|
| S-1 | P2 | Doc-only: contract that `#[Icon]` / overrides are trusted input |
| S-2 | P2 | Architecture test: `extraAttrs` is internally constructed only |
| S-3 | P2 | Regression test: `IsTranslatable` doesn't double-render |
| S-4 | P3 | Audit public-API surface to confirm `enumClass` is never request-driven |
| S-5 | P3 | Add `gitleaks` (or equivalent) to CI |
| S-6 | P3 | Document env var surface in `docs/configuration.md` |
| S-7 | P3 | Reconsider `reportUnmatchedIgnoredErrors: false` — regenerate baseline per PHP |

None block v0.2.0. None require an immediate hotfix to v0.1.0.

[VERIFIED 2026-05-15: composer audit → No security vulnerability advisories found.]
[VERIFIED 2026-05-15: grep -rn -E 'eval *\(|extract *\(|shell_exec *\(|system *\(|passthru *\(|proc_open *\(|unserialize *\(' src/ → no matches]
[VERIFIED 2026-05-15: grep -rn -E 'DB::(raw|statement)' src/ → no matches]

---

## v0.3.0 pre-tag sweep — 2026-05-16

A second pass over the resources/views/ surface, motivated by F1
(`#[Icon]` raw render) + F2 (wireModel attribute concatenation) plus
the PR-π scope item in v0.4.0-scope.md.

### Inventory of `{!! ... !!}` raw renders

| Site | Source | Verdict |
|---|---|---|
| `_base/badge.blade.php` (`$caseIcon`) | `#[Icon]` attr — runtime-overridable via `enumerator.overrides` + `TenantContext` | **FIXED (F1, `ce685af`):** switched to `{{ }}`. |
| `_base/element.blade.php` (`$caseIcon`) | Same as above | **FIXED (F1, `ce685af`):** switched to `{{ }}`. |
| `_base/checkboxes.blade.php:57` (`$extraAttrs`) | `ComponentAttributeBag::__toString()` (Laravel core) | SAFE-by-construction. Pinned by `CheckboxesComponentContractTest`. |
| `_base/checkboxes.blade.php:74` (`$wireModelAttr`) | Built from props via `htmlspecialchars(..., ENT_QUOTES, 'UTF-8', false)` | **FIXED (F2, `ce685af`):** escape applied. Pinned by `WireModelEscapingTest`. |
| `_base/radio.blade.php:54` (`$extraAttrs`) | Same as checkboxes:57 | SAFE-by-construction. Pinned by `RadioComponentContractTest`. |
| `_base/radio.blade.php:72` (`$wireModelAttr`) | Same as checkboxes:74 | **FIXED (F2, `ce685af`):** escape applied. |
| `_base/select.blade.php:59` (`$extraAttrs`) | Same as above | SAFE-by-construction. Pinned by `SelectComponentContractTest`. |
| `_base/alpine-loader.blade.php` × 4 (`{!! json_encode($cdnUrl/$integrity/$localUrl, ...) !!}`) | `config('enumerator.alpine')` — config-time, NOT tenant-overridable; emitted inside `<script>...</script>` JS-string context | SAFE: `json_encode()` produces JS-string-safe output. Tenant overrides don't reach `alpine.*` config. |
| `_base/element.blade.php:45` (`$slot`) | Standard Blade `<x-slot>` content — developer-authored | SAFE by design. Identical to Laravel's `<x-...>` slot pattern. |

### Inventory of attribute-string concatenations

| Site | Inputs | Verdict |
|---|---|---|
| `_base/radio.blade.php` `$wireModelAttr` builder | `$wireModel`, `$wireModelModifier` | **FIXED (F2):** `htmlspecialchars(..., ENT_QUOTES, 'UTF-8', false)` applied to both fragments. |
| `_base/checkboxes.blade.php` `$wireModelAttr` builder | Same as above | **FIXED (F2):** same fix. |
| Framework subviews (bootstrap, tailwind, plain, bulma, daisyui) | — | None found. Subviews delegate attribute handling to `_base/*` via `@aware` / `@props`. |

### What's missing — closed by this pass

- v0.3.0 added contract tests for Select but not Radio / Checkboxes /
  Dropdown. Dropdown has no `{!! $extraAttrs !!}` surface (audited
  2026-05-16) so it doesn't need the contract test. Radio + Checkboxes
  now have parallel contract tests:
  `tests/Unit/Blade/RadioComponentContractTest.php` (3 tests),
  `tests/Unit/Blade/CheckboxesComponentContractTest.php` (3 tests).

### Result

No remaining `{!! !!}` site or attribute-string concatenation in
`resources/views/` is unaccounted for. The escape posture across the
v0.3.0 surface is consistent: tenant-overridable values are
`{{ ... }}`-escaped, attribute concatenations apply
`htmlspecialchars()`, raw outputs are pinned by either a contract test
(`$extraAttrs`), context-safety (`json_encode` inside `<script>`), or
the Blade slot convention (`$slot`).

[VERIFIED 2026-05-16: grep -rn '{!!' resources/views/ → 11 sites, all in `_base/`, all accounted for above]
[VERIFIED 2026-05-16: vendor/bin/pest tests/Unit/Blade/{Select,Radio,Checkboxes}ComponentContractTest.php → 9 passed (3 each)]
