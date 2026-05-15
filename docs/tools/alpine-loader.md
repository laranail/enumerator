# Alpine.js loader

Some of the package's Blade components (notably the searchable /
clearable `<x-...::dropdown>`, landing in PR-G) use Alpine.js for
interactive behaviour. The package ships an opt-in `<x-laranail-enumerator::alpine-loader />`
Blade component that handles loading Alpine for you: CDN-first, with
a local fallback, with a conflict check so it never double-loads.

## Quick start

Drop the loader once into your layout, in `<head>` or just before
`</body>`:

```blade
<!doctype html>
<html lang="en">
<head>
    {{-- ... --}}
    <x-laranail-enumerator::alpine-loader />
</head>
<body>
    {{-- ... --}}
</body>
</html>
```

That's it. Any page that includes the layout (and isn't already
loading Alpine via npm) now has Alpine.

## How it works

The component emits a tiny inline script that:

1. **Conflict check.** If `window.Alpine` is already defined (e.g. you
   imported `alpinejs` in your `app.js`), the loader no-ops. No
   double-load.
2. **CDN-first.** Inserts a `<script defer src="{CDN URL}"
   integrity="..." crossorigin="anonymous">`. The pinned version and
   SRI integrity hash come from `config('enumerator.alpine')`.
3. **Local fallback on error.** The CDN script's `onerror` handler
   swaps the source to the locally-published file at
   `/vendor/laranail-enumerator/alpine.min.js`. This requires you to
   have run:

   ```bash
   php artisan vendor:publish --tag=enumerator-js
   ```

   once. The bundle copies to `public/vendor/laranail-enumerator/alpine.min.js`.

## Configuration

`config/enumerator.php`:

```php
'alpine' => [
    'version'   => env('ENUMERATOR_ALPINE_VERSION', '3.15.12'),
    'integrity' => env('ENUMERATOR_ALPINE_INTEGRITY', 'sha384-...'),
    'cdn_url'   => env('ENUMERATOR_ALPINE_CDN', 'https://cdn.jsdelivr.net/npm/alpinejs@{version}/dist/cdn.min.js'),
    'local_url' => env('ENUMERATOR_ALPINE_LOCAL', '/vendor/laranail-enumerator/alpine.min.js'),
],
```

- `version` — Alpine major.minor.patch. The `{version}` placeholder in
  `cdn_url` is substituted at render time.
- `integrity` — SHA-384 hash for SRI. Set to an empty string to disable
  SRI (e.g. for a CDN that doesn't return CORS headers). The bundled
  `resources/js/alpine.min.js` and this value are kept in sync; CI
  verifies the match on every push.
- `cdn_url` — pattern. Default uses jsDelivr.
- `local_url` — fallback URL. Must be reachable from the browser.

## Opt-out — local only

If you're in a strict-CSP environment or always-offline context, skip
the CDN entirely:

```blade
<x-laranail-enumerator::alpine-loader :cdn="false" />
```

The component loads directly from `local_url`. You still need to have
run `vendor:publish --tag=enumerator-js`.

## Opt-out — you already ship Alpine

If you already load Alpine through npm + Vite (`import 'alpinejs'` in
`app.js`), simply omit `<x-laranail-enumerator::alpine-loader />`
from your layout. The Alpine-enhanced components detect the global
`window.Alpine` and work the same.

(You can also leave the loader in place — its conflict check will
no-op when `window.Alpine` is already defined.)

## Pinned version + "stay ahead"

The package pins a specific Alpine version. CI verifies the bundled
`resources/js/alpine.min.js` file's SHA-384 matches
`config('enumerator.alpine.integrity')` on every push, so the bundle
can't drift from the integrity hash without breaking the build.

When a new Alpine version is released:

1. Update `'version'` in `config/enumerator.php`.
2. Re-download the matching `cdn.min.js` to `resources/js/alpine.min.js`.
3. Recompute the SHA-384 SRI and update `'integrity'`.
4. CI passes → ship.

See `docs/release.md` for the release process.

## CSP considerations

If your Content-Security-Policy header blocks third-party CDNs:

- Add `https://cdn.jsdelivr.net` to your `script-src` directive, OR
- Use `:cdn="false"` and serve only the local copy.

The component emits an inline `<script>` for the bootstrap shim
itself; you'll need either `'unsafe-inline'` in `script-src` or a
nonce. To use a nonce, wrap the component:

```blade
<script nonce="{{ csp_nonce() }}">
    // ... if you want to bring the bootstrap logic under your own nonce ...
</script>
```

(In v0.2.0 the component does not accept a nonce attribute. If you
need it as a prop, file an issue.)

## Footprint

- Bundle size: ~46 KB minified for Alpine 3.15.12.
- Render overhead: the component emits ~25 lines of inline JavaScript;
  zero PHP overhead beyond constructor + config read.
- Page weight cost: one extra `<script>` tag in `<head>`.

## Why not npm + Vite first-class

Many Laravel projects (especially server-rendered Blade apps without
a frontend build step) don't have a Vite pipeline. Requiring `npm
install alpinejs` would force those consumers into a build chain just
to use one dropdown. The CDN-first + local-fallback pattern works in
both worlds: zero install if your network allows the CDN; one
`vendor:publish` if it doesn't.
