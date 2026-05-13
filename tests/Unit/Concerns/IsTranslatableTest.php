<?php

declare(strict_types=1);

use Illuminate\Translation\Translator;
use Simtabi\Laranail\Enumerator\Helpers\Humanizer;
use Simtabi\Laranail\Enumerator\Presets\Enums\StatusEnum;
use Simtabi\Laranail\Enumerator\Tests\Fixtures\Enums\TranslatableStatusEnum;

// IsTranslatable — full 3-tier resolution chain
// (Translator → #[Label] → humanize) via the bound TranslatorAdapter.

it('translationKey() composes namespace::enums.slug.case', function (): void {
    $key = TranslatableStatusEnum::translationKey('draft');
    expect($key)->toBe('enumerator-fixtures::enums.translatable_status.draft');
});

it('translationKey() includes the field suffix when provided', function (): void {
    $key = TranslatableStatusEnum::translationKey('draft', 'description');
    expect($key)->toBe('enumerator-fixtures::enums.translatable_status.draft.description');
});

it('translationSlug() snake-cases the class basename and strips trailing Enum', function (): void {
    expect(StatusEnum::translationSlug())->toBe('status');
});

it('label() falls back to the #[Label] attribute when no translation is registered', function (): void {
    expect(TranslatableStatusEnum::Draft->label())->toBe('Draft');
});

it('label() returns the registered translation when present', function (): void {
    /** @var Translator $translator */
    $translator = app('translator');
    $translator->addLines([
        'enums.translatable_status.draft' => 'Brouillon',
    ], 'fr', 'enumerator-fixtures');

    expect(TranslatableStatusEnum::Draft->label('fr'))->toBe('Brouillon');
});

it('description() and help() return null without a translation or #[Description]/#[Help]', function (): void {
    expect(StatusEnum::Active->description())->toBeNull();
    expect(StatusEnum::Active->help())->toBeNull();
});

it('description() reads the deep translation key', function (): void {
    /** @var Translator $translator */
    $translator = app('translator');
    $translator->addLines([
        'enums.translatable_status.draft.description' => 'A work in progress.',
    ], 'en', 'enumerator-fixtures');

    expect(TranslatableStatusEnum::Draft->description())->toBe('A work in progress.');
});

it('humanize() converts case names to a presentable label', function (): void {
    expect(Humanizer::humanize('Active'))->toBe('Active');
    expect(Humanizer::humanize('SuperActive'))->toBe('Super Active');
});

it('translationNamespace() reflects the per-enum override', function (): void {
    expect(TranslatableStatusEnum::translationNamespace())->toBe('enumerator-fixtures');
});

it('translationNamespace() falls back to the config default', function (): void {
    // StatusEnum doesn't override translationNamespace.
    expect(StatusEnum::translationNamespace())->toBe('enumerator');
});
