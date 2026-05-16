<?php

declare(strict_types=1);

use Illuminate\Translation\Translator;
use Simtabi\Laranail\Enumerator\Translations\LaravelTranslatorAdapter;

// LaravelTranslatorAdapter — default Lang-wrapping adapter.

it('translate() returns null when no translation is registered', function (): void {
    $adapter = new LaravelTranslatorAdapter;
    expect($adapter->translate('enumerator::nope.missing'))->toBeNull();
});

it('translate() returns the registered translation', function (): void {
    /** @var Translator $translator */
    $translator = app('translator');
    $translator->addLines(['nope.present' => 'Hello'], 'en', 'enumerator');

    $adapter = new LaravelTranslatorAdapter;
    expect($adapter->translate('enumerator::nope.present'))->toBe('Hello');
});

it('translate() respects the locale argument', function (): void {
    /** @var Translator $translator */
    $translator = app('translator');
    $translator->addLines(['scope.greeting' => 'Bonjour'], 'fr', 'enumerator');

    $adapter = new LaravelTranslatorAdapter;
    expect($adapter->translate('enumerator::scope.greeting', [], 'fr'))->toBe('Bonjour');
});

it('translate() interpolates replace parameters', function (): void {
    /** @var Translator $translator */
    $translator = app('translator');
    $translator->addLines(['scope.welcome' => 'Hi :name'], 'en', 'enumerator');

    $adapter = new LaravelTranslatorAdapter;
    expect($adapter->translate('enumerator::scope.welcome', ['name' => 'Imani']))
        ->toBe('Hi Imani');
});

it('has() returns true when a translation exists', function (): void {
    /** @var Translator $translator */
    $translator = app('translator');
    $translator->addLines(['scope.flag' => 'yes'], 'en', 'enumerator');

    $adapter = new LaravelTranslatorAdapter;
    expect($adapter->has('enumerator::scope.flag'))->toBeTrue();
});

it('has() returns false when no translation exists', function (): void {
    $adapter = new LaravelTranslatorAdapter;
    expect($adapter->has('enumerator::nope.totally-missing'))->toBeFalse();
});

it('setLocale() and getLocale() round-trip', function (): void {
    $adapter = new LaravelTranslatorAdapter;
    $original = $adapter->getLocale();
    try {
        $adapter->setLocale('de');
        expect($adapter->getLocale())->toBe('de');
    } finally {
        $adapter->setLocale($original);
    }
});

it('translate() treats an empty-string registered translation as a miss', function (): void {
    /** @var Translator $translator */
    $translator = app('translator');
    $translator->addLines(['scope.empty' => ''], 'en', 'enumerator');

    $adapter = new LaravelTranslatorAdapter;
    // empty stored value is reported by Lang::has() as present, but the
    // adapter normalises it to null so callers' fallback chains run.
    expect($adapter->translate('enumerator::scope.empty'))->toBeNull();
});

it('translate() returns null when the translator returns the key as-is (no fallback resolution)', function (): void {
    // Lang::get can return the key itself when no match exists and
    // fallbacks are exhausted. The adapter normalises this to null
    // so the caller's own fallback chain runs instead.
    $adapter = new LaravelTranslatorAdapter;
    expect($adapter->translate('enumerator::scope.no-such-entry'))->toBeNull();
});

it('has() respects the locale argument', function (): void {
    /** @var Translator $translator */
    $translator = app('translator');
    $translator->addLines(['scope.localised' => 'Hola'], 'es', 'enumerator');

    $adapter = new LaravelTranslatorAdapter;
    expect($adapter->has('enumerator::scope.localised', 'es'))->toBeTrue();
    expect($adapter->has('enumerator::scope.localised', 'fr'))->toBeFalse();
});

// F8 (pre-tag 2026-05-16) strict-locale behaviour: when an explicit
// non-null locale is passed, Lang's configured fallback chain is
// suppressed so IsTranslatable's own #[Label] / humanize fallback can
// take over. These tests pin that behaviour against future regressions.

it('translate() with explicit locale does NOT silently fall back to the app default', function (): void {
    /** @var Translator $translator */
    $translator = app('translator');
    // Register the key ONLY under the app default locale ('en' in Testbench).
    $translator->addLines(['scope.en-only-strict' => 'Hi'], 'en', 'enumerator');

    $adapter = new LaravelTranslatorAdapter;
    // Asking for 'fr' explicitly must NOT yield the 'en' value — the
    // caller's null-fallback chain depends on this.
    expect($adapter->translate('enumerator::scope.en-only-strict', [], 'fr'))->toBeNull();
});

it('translate() with null locale DOES allow the configured fallback chain', function (): void {
    /** @var Translator $translator */
    $translator = app('translator');
    $translator->addLines(['scope.default-locale-only' => 'Hi'], 'en', 'enumerator');

    $adapter = new LaravelTranslatorAdapter;
    // No explicit locale — Laravel's normal Lang::get behaviour applies
    // (which under the Testbench default locale 'en' returns the entry).
    expect($adapter->translate('enumerator::scope.default-locale-only'))->toBe('Hi');
});

it('has() with explicit locale only checks that locale (no fallback)', function (): void {
    /** @var Translator $translator */
    $translator = app('translator');
    $translator->addLines(['scope.has-strict' => 'Hi'], 'en', 'enumerator');

    $adapter = new LaravelTranslatorAdapter;
    expect($adapter->has('enumerator::scope.has-strict', 'fr'))->toBeFalse();
    expect($adapter->has('enumerator::scope.has-strict', 'en'))->toBeTrue();
    // null locale → Lang's normal behaviour applies, the key resolves
    // through the configured default locale.
    expect($adapter->has('enumerator::scope.has-strict'))->toBeTrue();
});
