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
