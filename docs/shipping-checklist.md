# Shipping checklist

1. `composer validate --strict`
2. `vendor/bin/pint --test`
3. `vendor/bin/phpstan analyse`
4. `vendor/bin/pest --coverage --min=90`
5. Update `CHANGELOG.md` (`[Unreleased]` → `[X.Y.Z]`).
6. Tag `vX.Y.Z` on the main branch.
7. Push the tag — GitHub Actions `release.yml` generates release notes.
8. Verify on Packagist.
