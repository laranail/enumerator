<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;

// Feature coverage for <x-laranail-enumerator::alpine-loader />.
//
// The component is intentionally framework-agnostic: it emits a
// <script> tag, not styled markup, so there is no per-framework
// view bundle. See src/Blade/Components/AlpineLoader.php and
// docs/tools/alpine-loader.md for the contract.

beforeEach(function (): void {
    // Reset to known config so individual tests can override.
    config()->set('enumerator.alpine', [
        'version' => '3.15.12',
        'integrity' => 'sha384-pb6hrQvo4s23cEUFtj0CZkzGE3jyK3pj26RIupXXxhSrrcUA/Cn0lZgcCrGH0t6L',
        'cdn_url' => 'https://cdn.jsdelivr.net/npm/alpinejs@{version}/dist/cdn.min.js',
        'local_url' => '/vendor/laranail-enumerator/alpine.min.js',
    ]);
});

it('emits a script element and the conflict-check guard', function (): void {
    $html = Blade::render('<x-laranail-enumerator::alpine-loader />');

    expect($html)
        ->toContain('<script>')
        ->toContain('window.Alpine')
        ->toContain('document.createElement(\'script\')')
        ->toContain('script.defer = true');
});

it('defaults to CDN-first and includes the pinned version + SRI', function (): void {
    $html = Blade::render('<x-laranail-enumerator::alpine-loader />');

    expect($html)
        ->toContain('cdn.jsdelivr.net/npm/alpinejs@3.15.12')
        ->toContain('script.integrity')
        ->toContain('sha384-pb6hrQvo4s23cEUFtj0CZkzGE3jyK3pj26RIupXXxhSrrcUA/Cn0lZgcCrGH0t6L')
        ->toContain('crossOrigin');
});

it('includes the local-fallback onerror handler when CDN is enabled', function (): void {
    $html = Blade::render('<x-laranail-enumerator::alpine-loader />');

    expect($html)
        ->toContain('script.onerror')
        ->toContain('/vendor/laranail-enumerator/alpine.min.js');
});

it('skips the CDN attempt entirely when :cdn=\'false\'', function (): void {
    $html = Blade::render('<x-laranail-enumerator::alpine-loader :cdn="false" />');

    expect($html)
        ->toContain('/vendor/laranail-enumerator/alpine.min.js')
        ->not->toContain('cdn.jsdelivr.net')
        ->not->toContain('script.onerror');
});

it('omits the integrity attribute when the config value is empty', function (): void {
    config()->set('enumerator.alpine.integrity', '');
    $html = Blade::render('<x-laranail-enumerator::alpine-loader />');

    expect($html)
        ->not->toContain('script.integrity')
        ->not->toContain('crossOrigin');
});

it('substitutes the version placeholder in the CDN URL pattern', function (): void {
    config()->set('enumerator.alpine.version', '3.14.0');
    config()->set('enumerator.alpine.cdn_url', 'https://example.test/alpine@{version}.js');
    config()->set('enumerator.alpine.integrity', ''); // skip SRI for the alternate CDN

    $html = Blade::render('<x-laranail-enumerator::alpine-loader />');

    expect($html)->toContain('https://example.test/alpine@3.14.0.js');
});
