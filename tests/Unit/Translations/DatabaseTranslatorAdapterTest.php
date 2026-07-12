<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Simtabi\Laranail\Enumerator\Translations\DatabaseTranslatorAdapter;

// DatabaseTranslatorAdapter — reference DB-backed translation source.

beforeEach(function (): void {
    Schema::create('enum_translations', function ($table): void {
        $table->id();
        $table->string('key');
        $table->string('locale');
        $table->text('value');
    });

    DB::table('enum_translations')->insert([
        ['key' => 'enumerator::enums.demo.active', 'locale' => 'en', 'value' => 'Active'],
        ['key' => 'enumerator::enums.demo.active', 'locale' => 'fr', 'value' => 'Actif'],
        ['key' => 'enumerator::enums.demo.welcome', 'locale' => 'en', 'value' => 'Hi :name'],
        ['key' => 'enumerator::enums.demo.empty',   'locale' => 'en', 'value' => ''],
    ]);
});

afterEach(function (): void {
    Schema::dropIfExists('enum_translations');
});

it('translate() returns the row for the active locale', function (): void {
    app()->setLocale('en');
    $adapter = new DatabaseTranslatorAdapter;
    expect($adapter->translate('enumerator::enums.demo.active'))->toBe('Active');
});

it('translate() respects an explicit locale', function (): void {
    $adapter = new DatabaseTranslatorAdapter;
    expect($adapter->translate('enumerator::enums.demo.active', [], 'fr'))->toBe('Actif');
});

it('translate() returns null when no row matches', function (): void {
    $adapter = new DatabaseTranslatorAdapter;
    expect($adapter->translate('enumerator::enums.demo.nope'))->toBeNull();
});

it('translate() applies :placeholder replacements', function (): void {
    app()->setLocale('en');
    $adapter = new DatabaseTranslatorAdapter;
    expect($adapter->translate('enumerator::enums.demo.welcome', ['name' => 'Imani']))
        ->toBe('Hi Imani');
});

it('translate() treats empty stored value as a miss', function (): void {
    $adapter = new DatabaseTranslatorAdapter;
    expect($adapter->translate('enumerator::enums.demo.empty', [], 'en'))->toBeNull();
});

it('has() reflects the table contents', function (): void {
    $adapter = new DatabaseTranslatorAdapter;
    expect($adapter->has('enumerator::enums.demo.active', 'en'))->toBeTrue();
    expect($adapter->has('enumerator::enums.demo.nope', 'en'))->toBeFalse();
});

it('setLocale() and getLocale() round-trip', function (): void {
    $adapter = new DatabaseTranslatorAdapter;
    $adapter->setLocale('de');
    expect($adapter->getLocale())->toBe('de');
});

it('lookup is cached per key|locale and clears via flush()', function (): void {
    $adapter = new DatabaseTranslatorAdapter;
    expect($adapter->translate('enumerator::enums.demo.active', [], 'en'))->toBe('Active');

    // Mutate the underlying row.
    DB::table('enum_translations')
        ->where('key', 'enumerator::enums.demo.active')
        ->where('locale', 'en')
        ->update(['value' => 'Active!']);

    // Cached result still wins.
    expect($adapter->translate('enumerator::enums.demo.active', [], 'en'))->toBe('Active');

    // After flush(), the new value is picked up.
    $adapter->flush();
    expect($adapter->translate('enumerator::enums.demo.active', [], 'en'))->toBe('Active!');
});

it('translate() falls through gracefully when the table does not exist', function (): void {
    Schema::dropIfExists('enum_translations');
    $adapter = new DatabaseTranslatorAdapter;
    expect($adapter->translate('enumerator::enums.demo.active', [], 'en'))->toBeNull();
});

it('accepts a custom table and column names', function (): void {
    Schema::create('custom_i18n', function ($table): void {
        $table->id();
        $table->string('translation_key');
        $table->string('locale_code');
        $table->text('translation_value');
    });
    DB::table('custom_i18n')->insert([
        'translation_key' => 'app.greeting',
        'locale_code' => 'en',
        'translation_value' => 'Howdy',
    ]);

    $adapter = new DatabaseTranslatorAdapter(
        table: 'custom_i18n',
        keyColumn: 'translation_key',
        localeColumn: 'locale_code',
        valueColumn: 'translation_value',
    );

    expect($adapter->translate('app.greeting', [], 'en'))->toBe('Howdy');

    Schema::dropIfExists('custom_i18n');
});
