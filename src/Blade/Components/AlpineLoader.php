<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Blade\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

/**
 * Loads Alpine.js for the Alpine-enhanced Blade components (notably the
 * searchable / clearable dropdown). CDN-first with local fallback.
 *
 * Drop into a layout once, in <head> or before </body>:
 *
 *     <x-laranail-enumerator::alpine-loader />
 *
 * Behavior:
 *
 *   1. Conflict / presence check — if `window.Alpine` is already
 *      defined (e.g. the consumer ships Alpine via npm + Vite), the
 *      loader no-ops. The package never double-loads Alpine.
 *   2. CDN-first — emits `<script defer src="{cdn_url}"
 *      integrity="..." crossorigin="anonymous">`. The pinned version
 *      and SRI integrity hash come from `config('enumerator.alpine')`.
 *   3. Local fallback on CDN error — the script's onerror handler
 *      swaps the source to the locally-published file under
 *      `/vendor/laranail-enumerator/alpine.min.js`. Requires that the
 *      consumer has run `php artisan vendor:publish --tag=enumerator-js`.
 *   4. Opt-out — pass `:cdn="false"` to skip the CDN entirely. Useful
 *      under strict CSP policies that block external script sources.
 *
 * Component is intentionally framework-agnostic (no
 * `RoutesToFrameworkView` trait) — it emits a <script> tag, not styled
 * markup. The base view is always
 * `_base/alpine-loader.blade.php`.
 *
 * Pinned version + SRI integrity live in `config/enumerator.php` under
 * `alpine.version` / `alpine.integrity`. CI verifies that the pinned
 * hash matches the bundled `resources/js/alpine.min.js` file on every
 * push (see `.github/workflows/ci.yml`).
 */
class AlpineLoader extends Component
{
    public string $version;

    public string $integrity;

    public string $cdnUrl;

    public string $localUrl;

    public function __construct(
        public bool $cdn = true,
    ) {
        /** @var array{version?: string, integrity?: string, cdn_url?: string, local_url?: string} $cfg */
        $cfg = (array) (function_exists('config') ? (config('enumerator.alpine') ?? []) : []);
        $this->version = (string) ($cfg['version'] ?? '3.15.12');
        $this->integrity = (string) ($cfg['integrity'] ?? '');
        $cdnTemplate = (string) ($cfg['cdn_url'] ?? 'https://cdn.jsdelivr.net/npm/alpinejs@{version}/dist/cdn.min.js');
        $this->cdnUrl = str_replace('{version}', $this->version, $cdnTemplate);
        $this->localUrl = (string) ($cfg['local_url'] ?? '/vendor/laranail-enumerator/alpine.min.js');
    }

    public function render(): View
    {
        return view('laranail-enumerator::components._base.alpine-loader');
    }
}
