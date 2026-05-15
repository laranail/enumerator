# ADR-0006 — Livewire is `require-dev`, exercised in CI

- **Status:** Accepted
- **Date:** 2026-05-15
- **Deciders:** Imani Manyara

## Context

ADR-0004 ships Livewire-aware Blade components in v0.2.0. The Testbench
suite can only exercise these components if Livewire is actually
installed during CI. Two options:

- **`suggest` only** — Consumer opts in; no CI coverage of the
  integration files. (This is the current v0.1.0 posture for the
  existing `src/Integrations/Livewire/EnumeratorCasts.php`.)
- **`require-dev`** — Livewire is installed in `composer install` so
  CI can run the integration tests.

## Decision

`livewire/livewire: ^3.5` joins `require-dev` in `composer.json`. The
Livewire integration files (existing `src/Integrations/Livewire/*` and
the new v0.2.0 components) are removed from `phpstan.neon`'s
`excludePaths` so static analysis exercises them. Feature tests under
`tests/Feature/Integrations/Livewire/` run during the standard `pest`
gate on all three PHP versions in the CI matrix.

Livewire stays in `suggest` (or moves there in narrative) so consumers
know they need to install it to actually use the integration.

## Consequences

- CI run time grows (Livewire is non-trivial to install). Cache hit on
  `~/.composer/cache/files` already exists; new packages cache to
  install on first run.
- Livewire integration files leave the PHPStan exclusion list. Expect
  baseline entries to be added for genuine type-safety issues, OR
  fix them. This is the first time the integration surface has been
  under static analysis — surprises possible.
- `EnumeratorCasts.php` (existing v0.1.0 file) gains feature tests
  for the first time, raising overall coverage modestly.
- Filament and Nova stay in `suggest` only (different cost-benefit —
  Filament's dev install is heavier than Livewire's, and Nova requires
  a licence key).

## Alternatives considered

- **`require-dev` Livewire + Filament + Nova**: Out of scope. Nova
  needs a Composer auth token in CI which we don't want to add.
  Deferred to v0.3.0 or beyond.
- **`suggest` only**: Loses the credibility win of "Livewire is
  actually green on CI." Rejected.
