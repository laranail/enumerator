<?php

declare(strict_types=1);

use Simtabi\Laranail\Enumerator\Presets\Enums\StatusEnum;
use Simtabi\Laranail\Enumerator\Support\OptionsArrayBuilder;
use Simtabi\Laranail\Enumerator\Tests\Fixtures\Enums\LegacyStatusEnum;

// OptionsArrayBuilder — shared [value => label] builder for Filament + Nova.

it('for() returns [value => label] for a backed enum', function (): void {
    $options = OptionsArrayBuilder::for(StatusEnum::class);
    expect($options['active'])->toBe('Active');
    expect($options['inactive'])->toBe('Inactive');
});

it('for() handles class-const enums', function (): void {
    $options = OptionsArrayBuilder::for(LegacyStatusEnum::class);
    expect($options)->toHaveKey('active');
});

it('for() returns an empty array for a non-enumerator class', function (): void {
    expect(OptionsArrayBuilder::for(stdClass::class))->toBe([]);
});

it('flipped() returns [label => value]', function (): void {
    $options = OptionsArrayBuilder::flipped(StatusEnum::class);
    expect($options['Active'])->toBe('active');
    expect($options['Inactive'])->toBe('inactive');
});
