# ADR-0005 — Alpine loader: CDN-first, local fallback, Blade component opt-in

- **Status:** Accepted
- **Date:** 2026-05-15
- **Deciders:** Imani Manyara

## Context

ADR-0004 commits v0.2.0 to Alpine-enhanced `<x-...::dropdown>`. The
question is HOW Alpine itself gets onto the page. Three patterns
exist in the Laravel ecosystem:

- **NPM + Vite** — requires the consumer to add `import 'alpinejs'` to
  `resources/js/app.js`. Adds a build-step dependency for users who
  don't already have one.
- **CDN** — `<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js">` — zero install, but a network
  dependency at first-paint and a CSP wrinkle for strict-policy apps.
- **Local fallback** — bundle a copy of `alpine.min.js` inside the
  package, publish to `public/vendor/laranail-enumerator/`.

Tusente ships none — leaves Alpine to the consumer. Botble bundles
its own JS. Other surveyed packages don't use Alpine.

## Decision

Ship a **`<x-laranail-enumerator::alpine-loader />`** Blade component
the user drops into their layout's `<head>` (or just before
`</body>`). The component's behaviour:

1. **Conflict / presence check.** If `window.Alpine` is already
   defined, the loader no-ops. The package never double-loads Alpine.
2. **CDN-first.** Emit a `<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@{pinned-version}/dist/cdn.min.js" onerror="…">`.
   The pinned version is updated in CI via Dependabot on a custom
   schedule (see consequences).
3. **Local fallback on CDN error.** The `onerror` handler swaps the
   script `src` to the locally-published
   `/vendor/laranail-enumerator/alpine.min.js`. This requires the
   consumer to have run
   `php artisan vendor:publish --tag=enumerator-js` once.
4. **Opt-out.** The component accepts a `cdn` boolean prop
   (`<x-...::alpine-loader :cdn="false" />`) for strict-CSP environments
   that need to skip the CDN attempt entirely.
5. **Version pin lives in `config/enumerator.php`** under
   `'alpine' => ['version' => '3.x.x', 'integrity' => 'sha384-…']`.
   Empty `integrity` value skips SRI (default for development).

## Consequences

- A new `resources/js/alpine.min.js` ships in the package, kept in sync
  with the pinned `config.alpine.version`. The CI workflow gets a step
  that re-downloads the pinned version, verifies the SRI, and fails the
  build on mismatch. (Verifies the "stay ahead" requirement.)
- A new publishable tag `enumerator-js` appears in
  `EnumeratorServiceProvider::bootPublishing()`.
- The component lives at `src/Blade/Components/AlpineLoader.php` plus
  view at `resources/views/components/_base/alpine-loader.blade.php`.
- A new docs/tools entry `docs/tools/alpine-loader.md` covers usage,
  the CSP gotcha, and how to disable the CDN.
- The CDN URL is hardcoded; if jsDelivr is ever blocked, the consumer
  swaps the file via `vendor:publish`. The pinned `integrity` hash
  protects against CDN tampering when SRI is wired.

## Alternatives considered

- **NPM + Vite first-class**: Adds a build-step dependency. Many
  consumers don't have Vite. Rejected.
- **Bundle Alpine inline as an inline `<script>` tag**: Doubles the
  initial HTML payload on every page that uses the dropdown.
  Rejected.
- **Local-first, CDN fallback**: Reverses the proposal. Local-first
  means every consumer must `vendor:publish` before the dropdown works.
  CDN-first is friendlier as the default; the local fallback is the
  safety net for strict-CSP or offline-first apps.
