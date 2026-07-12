# Contributing

Thanks for considering a contribution to `laranail/enumerator`. This guide
covers the workflow, conventions, and quality bar we expect from pull
requests.

## Workflow

1. Open an issue describing the change before writing code, unless the fix
   is small and obvious.
2. Fork the repository and create a feature branch off `main`.
3. Make the change, including tests and documentation.
4. Run the full quality gate locally — see "Quality gates" below.
5. Submit a pull request referencing the issue.

## Quality gates

Every pull request must pass:

```bash
composer validate --strict
composer dump-autoload --strict-psr
vendor/bin/pint --test
vendor/bin/phpstan analyse
vendor/bin/pest --coverage --min=90
```

If any gate fails, the PR is blocked. Fix the root cause; do not weaken the
gate.

## Conventions

- PHP 8.3+ syntax only. Use readonly classes/properties where data is
  immutable; use typed class constants where PHP allows.
- Strict types declared at the top of every PHP file:
  `declare(strict_types=1);`
- PSR-12 formatting via Laravel Pint (`pint.json`).
- Public types and methods must be typed and have PHPDoc only where types
  alone are insufficient (e.g. generic templates).
- Tests are written in Pest 3. New features ship with both unit and feature
  tests.
- Commits use imperative mood subject lines (≤ 72 chars). The body explains
  the *why*. No emoji.

## File and naming conventions

- PSR-4: `Simtabi\Laranail\Enumerator\` → `src/`.
- All preset enums end with the suffix `Enum`.
- All concerns start with `Has` or `Is` (state-of-being vs ability).
- Contracts live in `Contracts/`; attributes in `Attributes/`; service
  helpers in `Support/`.

## Reporting security issues

Do not file a public issue. Email `opensource@simtabi.com` per
[`SECURITY.md`](SECURITY.md).

## Code of conduct

Participation is governed by [`CODE_OF_CONDUCT.md`](CODE_OF_CONDUCT.md).
