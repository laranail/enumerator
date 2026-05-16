<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;
use Illuminate\Translation\Translator;
use Simtabi\Laranail\Enumerator\Tests\Fixtures\Enums\SimpleStatusEnum;

// PR-ρ i18n scaffolding: pin the dropdown's user-facing strings to
// the translation table. Before PR-ρ these were hardcoded English
// literals inside the Blade view + Alpine x-data — community
// translation PRs couldn't reach them. Now they flow through
// `__('enumerator::enumerator.components.dropdown.*')`.

beforeEach(function (): void {
    config()->set('enumerator.css_framework', 'plain');
});

it('dropdown uses the English defaults out of the box', function (): void {
    $html = (string) Blade::render(
        '<x-laranail-enumerator::dropdown :enum="$enum" name="status" :searchable="true" :clearable="true" :announce-changes="true" />',
        ['enum' => SimpleStatusEnum::class],
    );

    expect($html)
        ->toContain('placeholder="Search…"')
        ->toContain('aria-label="Search options"')
        ->toContain('aria-label="Clear selection"')
        ->toContain('No matches.');
});

it('dropdown picks up a community-supplied locale via vendor publish', function (): void {
    /** @var Translator $translator */
    $translator = app('translator');

    // Simulate the lang/vendor/enumerator/fr/enumerator.php overlay
    // that a consumer ships when they want French. addLines lets
    // tests stand in for vendor publish without writing files.
    $translator->addLines([
        'enumerator.components.dropdown.search_placeholder' => 'Rechercher…',
        'enumerator.components.dropdown.search_label' => 'Rechercher des options',
        'enumerator.components.dropdown.no_matches' => 'Aucun résultat.',
        'enumerator.components.dropdown.clear_selection' => 'Effacer la sélection',
        'enumerator.components.dropdown.remove_value' => 'Supprimer :label',
    ], 'fr', 'enumerator');

    app()->setLocale('fr');

    $html = (string) Blade::render(
        '<x-laranail-enumerator::dropdown :enum="$enum" name="status" :searchable="true" :clearable="true" />',
        ['enum' => SimpleStatusEnum::class],
    );

    expect($html)
        ->toContain('placeholder="Rechercher…"')
        ->toContain('aria-label="Rechercher des options"')
        ->toContain('aria-label="Effacer la sélection"')
        ->toContain('Aucun résultat.');
});

it('dropdown locale also reaches the Alpine x-data announcement strings', function (): void {
    /** @var Translator $translator */
    $translator = app('translator');
    $translator->addLines([
        'enumerator.components.dropdown.announce_added' => 'Ajouté :label',
        'enumerator.components.dropdown.announce_removed' => 'Supprimé :label',
        'enumerator.components.dropdown.announce_selected' => 'Sélectionné :label',
        'enumerator.components.dropdown.announce_cleared' => 'Sélection effacée',
    ], 'fr', 'enumerator');

    app()->setLocale('fr');

    $html = (string) Blade::render(
        '<x-laranail-enumerator::dropdown :enum="$enum" name="status" :multiple="true" :searchable="true" :announce-changes="true" />',
        ['enum' => SimpleStatusEnum::class],
    );

    // The announce_* prefixes are concatenated with the option label
    // in JS. They're emitted inside the x-data attribute, so `"`
    // gets HTML-entity-encoded as `&quot;`. The browser decodes
    // before Alpine reads, so the runtime behaviour is correct;
    // assert against the encoded form.
    expect($html)
        ->toContain('&quot;Ajouté &quot;')
        ->toContain('&quot;Supprimé &quot;')
        ->toContain('&quot;Sélectionné &quot;')
        ->toContain('&quot;Sélection effacée&quot;');
});

it('dropdown remove_value prefix uses the locale-supplied text', function (): void {
    /** @var Translator $translator */
    $translator = app('translator');
    $translator->addLines([
        'enumerator.components.dropdown.remove_value' => 'Quitar :label',
    ], 'es', 'enumerator');

    app()->setLocale('es');

    $html = (string) Blade::render(
        '<x-laranail-enumerator::dropdown :enum="$enum" name="status" :multiple="true" :searchable="true" />',
        ['enum' => SimpleStatusEnum::class],
    );

    // The Alpine pill aria-label is bound dynamically: prefix + entry.label.
    // Emitted inside a `:aria-label="..."` attribute so `"` is
    // HTML-entity-encoded as `&quot;`.
    expect($html)->toContain('&quot;Quitar &quot;');
});
