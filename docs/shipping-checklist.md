# Shipping checklist

## Per-release sequence

1. `composer validate --strict`
2. `vendor/bin/pint --test`
3. `vendor/bin/phpstan analyse`
4. `vendor/bin/pest --coverage --min=<current CI floor>` (target: 90 %; ratchets up as the unit-test backfill lands)
5. Update `CHANGELOG.md` (`[Unreleased]` → `[X.Y.Z] — YYYY-MM-DD`).
6. Tag `vX.Y.Z` on the `main` branch (`git tag -a vX.Y.Z -m "<subject>"`).
7. Push the tag — GitHub Actions `release.yml` auto-creates the release.
8. `gh release edit vX.Y.Z --notes-file <CHANGELOG block>` so the release page carries the full notes.
9. Verify on Packagist (auto-updates from the GitHub webhook once the package is registered — see "Pending external setup" below).

## Pending external setup (one-time, maintainer-only)

These are blocking on UI / credentials that need a human in front of a
browser. The package can ship without them — they affect distribution,
observability, and the badges-row on the README, not the code itself.

### 1. Packagist registration

- Go to <https://packagist.org/packages/submit>.
- Paste `https://github.com/laranail/enumerator` and submit.
- Click **"Use GitHub Hook"** on the resulting package page so future
  tag pushes auto-publish to Packagist without manual re-submission.
- Add the Packagist badge to `README.md` once the page is live:
  `[![Packagist](https://img.shields.io/packagist/v/laranail/enumerator.svg)](https://packagist.org/packages/laranail/enumerator)`.

Until this lands, the package isn't installable via
`composer require laranail/enumerator` — consumers can only use a
`composer.json` `repositories.path` pointer at a local checkout.

### 2. OSS portal pages on `opensource.simtabi.com`

Per Simtabi org conventions (`/Users/imanimanyara/Artisan/projects/opensource/CLAUDE.md`):

- Product landing page: <https://opensource.simtabi.com/products/laranail/enumerator>
- Product documentation: <https://opensource.simtabi.com/documentation/laranail/enumerator>

Both pages need to exist before the URLs in `composer.json` (`homepage`,
`support.docs`) resolve. The Simtabi org docs landing
(<https://opensource.simtabi.com/documentation/>) should also link to
the new product page.

### 3. `CODECOV_TOKEN` GitHub secret

Codecov v4 dropped tokenless uploads for public OSS repos. The
`.github/workflows/coverage.yml` workflow already targets Codecov v4
but the upload step fails with `Token required - not valid tokenless
upload` because the secret isn't set. The workflow uses
`fail_ci_if_error: false` so the build doesn't fail — the badge just
stays "unknown".

Steps:

1. Sign in to <https://app.codecov.io/> with the GitHub account that
   owns `laranail/enumerator`.
2. Add the repo (or click through if it already appears).
3. Copy the upload token from the Codecov repo settings.
4. In GitHub: `https://github.com/laranail/enumerator/settings/secrets/actions/new`
   — add a new repository secret named **`CODECOV_TOKEN`** with the
   pasted value.
5. The next push to `main` will upload successfully and the README
   badge will flip from "unknown" to a coverage percentage.

### 4. Infection baseline review

`.github/workflows/infection.yml` runs mutation testing with pcov on
every push to `main` (and on PRs touching `src/` / `tests/`). It's
currently `continue-on-error: true` because no kill-rate floor is
gated yet. After **2 – 3 CI runs** have produced data, set the floor:

1. Open the most recent infection workflow run + download the
   `infection-log` artifact (retains 14 days).
2. Inspect `infection.log` for the Mutation Score Indicator (MSI)
   and Covered Code MSI. Both are percentages.
3. Decide:
   - If the score looks healthy (≥ 60 % MSI), set
     `minMsi` / `minCoveredMsi` in `infection.json5` to the observed
     value minus a ~5 % buffer.
   - If surviving mutants reveal genuinely under-tested branches
     (most common in the first 1-2 runs), write tests killing those
     mutants first, re-run, then set the floor.
4. Flip `continue-on-error: true` → `false` in
   `.github/workflows/infection.yml` so the gate is actually
   enforcing.
5. Add the Infection badge to `README.md` once gated.

This follows the same coverage-then-gate discipline as v0.2.0 → v0.2.1
(don't raise the floor before the measurement sustains).

---

## See also

- [`docs/release.md`](release.md) — full release workflow details
- [`.github/workflows/release.yml`](../.github/workflows/release.yml) — what runs on tag push
- Org-wide package-publishing guide — local Simtabi maintainer reference at `/opensource/package-publishing-guide.md` (not shipped in this repo)
