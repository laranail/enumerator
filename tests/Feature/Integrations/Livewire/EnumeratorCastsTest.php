<?php

declare(strict_types=1);

use Simtabi\Laranail\Enumerator\Integrations\Livewire\EnumeratorCasts;
use Simtabi\Laranail\Enumerator\Tests\Fixtures\Enums\SimpleStatusEnum;

// Feature coverage for the Livewire integration's static
// EnumeratorCasts::hydrateProperty() helper. Livewire 3.5+ natively
// rehydrates BackedEnum-typed component properties, so this helper
// exists for the pure-enum and AbstractEnumeratorClass paths where
// the framework's native rehydration can't reach.

it('returns null when the incoming value is null', function (): void {
    $result = EnumeratorCasts::hydrateProperty(SimpleStatusEnum::class, null);

    expect($result)->toBeNull();
});

it('rehydrates a case-name string into the enum case', function (): void {
    // SimpleStatusEnum is a backed string enum that uses HasEnumerator
    // (the umbrella), so HasFromHelpers::tryFromName() is available.
    $result = EnumeratorCasts::hydrateProperty(
        SimpleStatusEnum::class,
        SimpleStatusEnum::Active->name,
    );

    expect($result)->toBe(SimpleStatusEnum::Active);
});

it('returns the original string when no case matches the name', function (): void {
    $result = EnumeratorCasts::hydrateProperty(
        SimpleStatusEnum::class,
        'NotACaseName',
    );

    expect($result)->toBe('NotACaseName');
});

it('returns the original value when the class is not an enum', function (): void {
    $result = EnumeratorCasts::hydrateProperty(
        stdClass::class,
        'Active',
    );

    expect($result)->toBe('Active');
});

it('returns the original value when the value is not a string', function (): void {
    $result = EnumeratorCasts::hydrateProperty(
        SimpleStatusEnum::class,
        42,
    );

    expect($result)->toBe(42);
});

it('returns the original value when the value is an array', function (): void {
    $result = EnumeratorCasts::hydrateProperty(
        SimpleStatusEnum::class,
        ['Active'],
    );

    expect($result)->toBe(['Active']);
});

it('passes through an already-hydrated enum instance unchanged', function (): void {
    // Edge case: Livewire's hook may call the helper with an instance
    // (not a string). The method should preserve it.
    $result = EnumeratorCasts::hydrateProperty(
        SimpleStatusEnum::class,
        SimpleStatusEnum::Active,
    );

    expect($result)->toBe(SimpleStatusEnum::Active);
});
