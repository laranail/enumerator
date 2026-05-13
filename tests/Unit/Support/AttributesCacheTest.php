<?php

declare(strict_types=1);

use Simtabi\Laranail\Enumerator\Exceptions\InvalidBitmaskException;
use Simtabi\Laranail\Enumerator\Presets\Enums\StatusEnum;
use Simtabi\Laranail\Enumerator\Support\AttributeBag;
use Simtabi\Laranail\Enumerator\Support\AttributesCache;
use Simtabi\Laranail\Enumerator\Tests\Fixtures\Enums\FlaggedPermissionEnum;
use Simtabi\Laranail\Enumerator\Tests\Fixtures\Enums\LegacyStatusEnum;

// AttributesCache — reflection cache for resolved #[Attribute] data.

beforeEach(function (): void {
    AttributesCache::flush();
});

it('for() returns an AttributeBag for a case', function (): void {
    $bag = AttributesCache::for(StatusEnum::Active);
    expect($bag)->toBeInstanceOf(AttributeBag::class);
    expect($bag->label)->toBe('Active');
    expect($bag->color)->toBe('success');
    expect($bag->icon)->toBe('check-circle');
    expect($bag->order)->toBe(10);
});

it('for() memoises the bag', function (): void {
    $first = AttributesCache::for(StatusEnum::Active);
    $second = AttributesCache::for(StatusEnum::Active);
    expect($first)->toBe($second);
});

it('for() works on AbstractEnumeratorClass instances', function (): void {
    $bag = AttributesCache::for(LegacyStatusEnum::ACTIVE());
    expect($bag->label)->toBe('Active');
    expect($bag->color)->toBe('success');
});

it('forClass() returns a class-level AttributeBag', function (): void {
    // StatusEnum has no class-level #[Description], so the bag is empty.
    $bag = AttributesCache::forClass(StatusEnum::class);
    expect($bag)->toBeInstanceOf(AttributeBag::class);
    expect($bag->description)->toBeNull();
});

it('bitFor() returns the bit assigned to a Bitwise case', function (): void {
    expect(AttributesCache::bitFor(FlaggedPermissionEnum::Read))->toBe(1);
    expect(AttributesCache::bitFor(FlaggedPermissionEnum::Admin))->toBe(8);
});

it('bitFor() throws when a case has no #[Bit] attribute', function (): void {
    AttributesCache::bitFor(StatusEnum::Active);
})->throws(InvalidBitmaskException::class);

it('validateBits() is idempotent for a valid bit set', function (): void {
    AttributesCache::validateBits(FlaggedPermissionEnum::class);
    AttributesCache::validateBits(FlaggedPermissionEnum::class);
    expect(true)->toBeTrue();
});

it('flush() clears the memo', function (): void {
    AttributesCache::for(StatusEnum::Active);
    expect(AttributesCache::snapshot())->not->toBe([]);
    AttributesCache::flush();
    expect(AttributesCache::snapshot())->toBe([]);
});

it('snapshot() and restore() round-trip via plain arrays', function (): void {
    AttributesCache::for(StatusEnum::Active);
    $snapshot = AttributesCache::snapshot();

    // Snapshots are arrays, not AttributeBag instances.
    foreach ($snapshot as $entry) {
        expect($entry)->toBeArray();
        expect($entry)->toHaveKey('label');
    }

    AttributesCache::flush();
    AttributesCache::restore($snapshot);

    // After restore, for() returns the cached bag.
    $bag = AttributesCache::for(StatusEnum::Active);
    expect($bag->label)->toBe('Active');
});
