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
