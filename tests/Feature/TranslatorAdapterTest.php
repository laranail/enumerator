<?php

declare(strict_types=1);

use Simtabi\Laranail\Enumerator\Contracts\TranslatorAdapter;
use Simtabi\Laranail\Enumerator\Tests\Fixtures\Enums\TranslatableStatusEnum;
use Simtabi\Laranail\Enumerator\Translations\LaravelTranslatorAdapter;

it('binds LaravelTranslatorAdapter by default', function (): void {
    $adapter = app(TranslatorAdapter::class);
    expect($adapter)->toBeInstanceOf(LaravelTranslatorAdapter::class);
});

it('wraps the Laravel translator for unconfigured translation keys', function (): void {
    // No translation registered for TranslatableStatusEnum::Draft → falls
    // through the chain: Lang::has() miss → #[Label] attribute hit.
    expect(TranslatableStatusEnum::Draft->label())->toBe('Draft');
});

it('returns null on missing keys via the adapter', function (): void {
    $adapter = app(TranslatorAdapter::class);
    $result = $adapter->translate('nope::definitely.missing.key');
    expect($result)->toBeNull();
});

it('lets consumers swap to a fake adapter at runtime', function (): void {
    $fake = new class implements TranslatorAdapter
    {
        public function translate(string $key, array $replace = [], ?string $locale = null): ?string
        {
            return $key === 'enumerator-fixtures::enums.translatable_status.draft.label'
                ? 'Brouillon'  // French
                : null;
        }

        public function has(string $key, ?string $locale = null): bool
        {
            return $this->translate($key, [], $locale) !== null;
        }

        public function setLocale(string $locale): void {}

        public function getLocale(): string
        {
            return 'fr';
        }
    };

    app()->instance(TranslatorAdapter::class, $fake);

    expect(TranslatableStatusEnum::Draft->label())->toBe('Brouillon');
    expect(TranslatableStatusEnum::Published->label())->toBe('Published'); // fell through to #[Label]
});
