<?php

declare(strict_types=1);

use Illuminate\Translation\Translator;
use Simtabi\Laranail\Enumerator\Tests\Fixtures\Enums\TranslatableLegacyCurrency;

/**
 * Translation on the class-constant path.
 *
 * The gap this closes: `AbstractEnumeratorClass` is the documented migration
 * target for legacy class-const enums, and those are precisely the enums that
 * already have a translation namespace. Native enums got `IsTranslatable`
 * through `BehaviorCore`; the class-const path did not, so migrating onto it
 * silently downgraded every translated label to a humanised case name. Nothing
 * errored, the strings just changed and the lang files went unused.
 */
it('resolves a registered translation for a class-const case', function (): void {
    /** @var Translator $translator */
    $translator = app('translator');
    $translator->addLines([
        'enums.currency.USD' => 'Dollar américain',
    ], 'fr', 'modules/core');

    expect(TranslatableLegacyCurrency::fromValue('USD')->label('fr'))
        ->toBe('Dollar américain');
});

it('falls back to the #[Label] attribute when nothing is registered', function (): void {
    expect(TranslatableLegacyCurrency::fromValue('USD')->label('de'))
        ->toBe('US Dollar');
});

it('falls back to humanising the case name with neither', function (): void {
    // The behaviour the class-const path had before translations were wired
    // in, kept intact so an enum with no lang files is unaffected — including
    // the humaniser's title-casing, which is why this is 'Kes' and not 'KES'.
    expect(TranslatableLegacyCurrency::fromValue('KES')->label())->toBe('Kes');
});

it('honours the overridden namespace and slug', function (): void {
    expect(TranslatableLegacyCurrency::translationKey('USD'))
        ->toBe('modules/core::enums.currency.USD');
});

it('resolves the field-specific description key', function (): void {
    /** @var Translator $translator */
    $translator = app('translator');
    $translator->addLines([
        'enums.currency.KES.description' => 'The Kenyan shilling',
    ], 'en', 'modules/core');

    expect(TranslatableLegacyCurrency::fromValue('KES')->description())
        ->toBe('The Kenyan shilling');
});

it('reads the case key from getValue() rather than an undefined property', function (): void {
    // IsTranslatable was written for native enums and read ->value and ->name
    // directly. On a class-const enum the backing property is private, so those
    // reads were undefined-property accesses that silently yielded nothing.
    /** @var Translator $translator */
    $translator = app('translator');
    $translator->addLines(['enums.currency.KES' => 'Shilingi'], 'sw', 'modules/core');

    expect(TranslatableLegacyCurrency::fromValue('KES')->label('sw'))->toBe('Shilingi');
});
