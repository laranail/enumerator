# Baseline — laranail/enumerator

Snapshot of every quality gate, captured 2026-05-15 on local toolchain.
Re-run any gate's command to re-derive the value — none of these are
authored figures.

## Toolchain

- `php -v` → PHP 8.5.0beta3 (cli) (built Sep 22 2025) NTS
- `composer --version` → installed via the system `composer`
- Project requires `^8.3`; CI matrix runs 8.3 + 8.4 + 8.5 with
  `fail-fast: false`.

## composer

| Command | Result |
|---|---|
| `composer install` | (skipped — `vendor/` was already present and gates ran clean) |
| `composer validate --strict` | `./composer.json is valid` |
| `composer audit` | `No security vulnerability advisories found.` |
| `composer show --direct` | larastan 3.9.6 / pint 1.29.1 / orchestra/testbench 11.1.0 / pest 3.8.6 / phpstan/extension-installer 1.4.3 / phpstan 2.1.54 |

Composer emits two Deprecation Notices when running on PHP 8.5 (one in
`justinrainbow/json-schema`, one in `Composer\Util\Http\CurlDownloader`).
These are in Composer's own code, not in the package, and are not
actionable here.

## Code style — Pint

```text
vendor/bin/pint --test
{"tool":"pint","result":"passed"}
```

## Static analysis — PHPStan

```text
vendor/bin/phpstan analyse --no-progress --memory-limit=2G
Note: Using configuration file phpstan.neon.
 [OK] No errors
```

PHPStan config (`phpstan.neon`):

- `level: max`
- `paths: [src, tests]`
- `excludePaths`: `tests/Unit/*`, `tests/Application/database/migrations/*`,
  `tests/Feature/Eloquent/HasEnumeratorScopesTest.php`,
  `tests/Feature/Eloquent/StateMachineModelTest.php`,
  `src/PHPStan/*`, `src/Integrations/*`
- `treatPhpDocTypesAsCertain: false`
- `reportUnmatchedIgnoredErrors: false` (baseline drift across PHP
  versions is silently tolerated — see security-audit P3 finding S-7)
- `ignoreErrors`: `new.static`, `generics.moreTypes`, `generics.notSubtype`
- Baseline file `phpstan-baseline.neon` is 393 KB

## Tests — Pest

```text
vendor/bin/pest --compact
  Tests:    2 skipped, 751 passed (1463 assertions)
  Duration: 9.85s
  Random Order Seed: 1778839326

  WARN  No code coverage driver available
```

Local has no Xdebug, so coverage isn't computable locally. CI is the
authority for coverage. Handoff doc reports CI value as **83.2%**
against a `--min=80` gate, last verified
`[VERIFIED 2026-05-14: CI run 8.3 job log → Total: 83.2 %]`. The audit
did not re-trigger CI to re-verify; the figure is held to
2026-05-14 freshness.

## Coverage gate inconsistency

Three surfaces report different minimum coverage values; only the CI
workflow is the binding gate.

| Surface | File | Claim |
|---|---|---|
| Binding gate | `.github/workflows/ci.yml:48` | `--min=80` |
| Composer script | `composer.json:94` | `--min=90` |
| Changelog narrative | `CHANGELOG.md:190` | "with 90% min coverage" |
| README narrative | `README.md:53` | "261 tests / 670 assertions" (also stale) |

Flagged as issue-register entry **I-2** (`docs/coverage drift`).

## phpunit.xml

- `bootstrap = vendor/autoload.php`
- `executionOrder = random`
- `failOnRisky = true`, `failOnWarning = true`,
  `beStrictAboutOutputDuringTests = true`
- 2 testsuites: `Unit` (`tests/Unit`), `Feature` (`tests/Feature`)
- Coverage source: `src` minus `src/PHPStan` and `src/Integrations`
- Coverage report: text-summary to stdout (no Cobertura / Clover —
  prevents Codecov-style integrations; flagged as I-9)

## Mutation testing

`infection` is not configured. Not blocking — flagged as gap **I-8**
in the issue register.

## Conclusion

All four gates that run locally are **green**. Coverage figure
(authoritative on CI only) is held at 83.2% from the
2026-05-14 verification; would need to retrigger CI to re-derive.

[VERIFIED 2026-05-15: pest exit code 0, 751 passed]
[VERIFIED 2026-05-15: phpstan exit code 0]
[VERIFIED 2026-05-15: pint --test exit code 0]
[VERIFIED 2026-05-15: composer validate --strict → valid]
[VERIFIED 2026-05-15: composer audit → No security vulnerability advisories found.]
