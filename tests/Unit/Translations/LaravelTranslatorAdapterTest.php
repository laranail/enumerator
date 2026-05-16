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
