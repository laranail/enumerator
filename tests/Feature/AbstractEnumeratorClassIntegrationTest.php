<?php

declare(strict_types=1);

use Simtabi\Laranail\Enumerator\Presets\Enums\StatusEnum;
use Simtabi\Laranail\Enumerator\Support\OptionsArrayBuilder;
use Simtabi\Laranail\Enumerator\Tests\Fixtures\Enums\LegacyStatusEnum;

// class-const enums (`AbstractEnumeratorClass` subclasses)
// must work through the same integration surface as native enums.

it('OptionsArrayBuilder::for() works for class-const enums', function (): void {
    $options = OptionsArrayBuilder::for(LegacyStatusEnum::class);

    expect($options)->toBe([
        'active' => 'Active',
        'inactive' => 'Inactive',
        'banned' => 'Banned',
    ]);
});

it('OptionsArrayBuilder::flipped() works for class-const enums', function (): void {
    $flipped = OptionsArrayBuilder::flipped(LegacyStatusEnum::class);

    expect($flipped)->toBe([
        'Active' => 'active',
        'Inactive' => 'inactive',
        'Banned' => 'banned',
    ]);
});

it('AnnotateEnumeratorCommand renders @method stubs for class-const enums', function (): void {
    $this->artisan('enumerator:annotate', ['class' => LegacyStatusEnum::class])
        ->expectsOutputToContain('@method static LegacyStatusEnum ACTIVE()')
        ->expectsOutputToContain('@method static LegacyStatusEnum INACTIVE()')
        ->expectsOutputToContain('@method static LegacyStatusEnum BANNED()')
        ->assertSuccessful();
});

it('AnnotateEnumeratorCommand still works for native enums (no regression)', function (): void {
    $this->artisan('enumerator:annotate', [
        'class' => StatusEnum::class,
    ])
        ->expectsOutputToContain('@method static StatusEnum Active()')
        ->assertSuccessful();
});

it('AnnotateEnumeratorCommand rejects non-enumerator classes', function (): void {
    $this->artisan('enumerator:annotate', ['class' => stdClass::class])
        ->expectsOutputToContain('not an enumerator')
        ->assertFailed();
});
