<?php

declare(strict_types=1);

use Simtabi\Laranail\Enumerator\Exceptions\InvalidBitmaskException;
use Simtabi\Laranail\Enumerator\Presets\Enums\StatusEnum;
use Simtabi\Laranail\Enumerator\Support\AttributeBag;
use Simtabi\Laranail\Enumerator\Support\AttributesCache;
use Simtabi\Laranail\Enumerator\Tests\Fixtures\Enums\ClassConstBitEnum;
use Simtabi\Laranail\Enumerator\Tests\Fixtures\Enums\ClassConstDuplicateBitEnum;
use Simtabi\Laranail\Enumerator\Tests\Fixtures\Enums\DuplicateBitEnum;
use Simtabi\Laranail\Enumerator\Tests\Fixtures\Enums\FlaggedPermissionEnum;
use Simtabi\Laranail\Enumerator\Tests\Fixtures\Enums\LegacyStatusEnum;
use Simtabi\Laranail\Enumerator\Tests\Fixtures\Enums\MixedBitEnum;
use Simtabi\Laranail\Enumerator\Tests\Fixtures\Enums\NonPowerOfTwoBitEnum;

// AttributesCache — reflection cache for resolved #[Attribute] data.

// The cache is process-global static state: flush on BOTH sides so the
// label overrides these tests inject can never leak into other files
// (Pest runs in random order — see the OptionsArrayBuilder flake).
afterEach(function (): void {
    AttributesCache::flush();
});

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

// === Branch coverage push (2026-05-16) =====================================
// The tests above exercise the happy paths. Below: every uncovered
// branch in src/Support/AttributesCache.php — bit validation edge
// cases, snapshot/restore filter branches, the "object without getKey()"
// path in for().

it('for() returns an empty bag for a non-enum object without getKey()', function (): void {
    // The for() method accepts `object`. For class-const-style objects
    // it walks the constant attributes via getKey() — when getKey() is
    // missing, the bag is empty (line 57-58 of AttributesCache.php).
    $plain = new stdClass;
    $bag = AttributesCache::for($plain);

    expect($bag)->toBeInstanceOf(AttributeBag::class);
    expect($bag->label)->toBeNull();
    expect($bag->color)->toBeNull();
});

it('validateBits() skips cases with no #[Bit] attribute (mixed enum)', function (): void {
    // MixedBitEnum::Plain has no #[Bit]; the other two do. validateBits
    // must iterate without erroring (line 128-130 continue branch).
    AttributesCache::validateBits(MixedBitEnum::class);

    // bitFor on a no-#[Bit] case still throws per its own guard.
    expect(fn () => AttributesCache::bitFor(MixedBitEnum::Plain))
        ->toThrow(InvalidBitmaskException::class);

    // bitFor on a #[Bit] case still returns its bit.
    expect(AttributesCache::bitFor(MixedBitEnum::Read))->toBe(1);
});

it('validateBits() throws on a duplicate #[Bit] value (native enum)', function (): void {
    expect(fn () => AttributesCache::validateBits(DuplicateBitEnum::class))
        ->toThrow(InvalidBitmaskException::class, 'Duplicate bit 1 in enum');
});

it('validateBits() throws on a non-power-of-two #[Bit] value', function (): void {
    expect(fn () => AttributesCache::validateBits(NonPowerOfTwoBitEnum::class))
        ->toThrow(InvalidBitmaskException::class, 'is not a positive power of two');
});

it('validateBits() handles the class-const enum path', function (): void {
    // ClassConstBitEnum extends AbstractEnumeratorClass, so enum_exists()
    // returns false and we fall through to the ReflectionConstants path
    // (line 142-156 of AttributesCache.php).
    AttributesCache::validateBits(ClassConstBitEnum::class);

    // Idempotent — second call short-circuits via memoised key.
    AttributesCache::validateBits(ClassConstBitEnum::class);
    expect(true)->toBeTrue();
});

it('validateBits() throws on a duplicate bit in a class-const enum', function (): void {
    expect(fn () => AttributesCache::validateBits(ClassConstDuplicateBitEnum::class))
        ->toThrow(InvalidBitmaskException::class, 'Duplicate bit 1 in enum');
});

it('snapshot() filters out non-AttributeBag entries from the memo', function (): void {
    // The snapshot guard at line 190-191 only serialises AttributeBag
    // instances — other memo entries (e.g. validateBits' sentinel keys,
    // future cache extensions) are skipped.
    AttributesCache::for(StatusEnum::Active);

    // validateBits() inserts a sentinel under "<class>::__bits_validated__".
    // That sentinel IS an AttributeBag (line 159 self::$memo[$key] = new AttributeBag).
    // To trigger the continue, we poke a non-bag value into the memo via
    // reflection — only test-internal, never via a public API in prod.
    $ref = new ReflectionClass(AttributesCache::class);
    $memo = $ref->getProperty('memo');
    $current = $memo->getValue();
    $current['injected::not-a-bag'] = 'plain-string';
    $memo->setValue(null, $current);

    $snapshot = AttributesCache::snapshot();
    expect($snapshot)->not->toHaveKey('injected::not-a-bag');
    // Real cases still snapshotted.
    expect($snapshot)->toHaveKey(StatusEnum::class . '::Active');
});

it('restore() ignores non-array payload entries', function (): void {
    // The restore guard at line 217-218 silently skips entries whose
    // payload isn't an array (defensive against malformed snapshot
    // input).
    AttributesCache::flush();

    AttributesCache::restore([
        'corrupt::entry' => 'not-an-array',
        StatusEnum::class . '::Active' => [
            'label' => 'restored-label',
            'description' => null,
            'color' => null,
            'icon' => null,
            'help' => null,
            'order' => null,
            'bit' => null,
            'meta' => null,
            'cssClasses' => [],
        ],
    ]);

    // The valid entry round-tripped.
    $bag = AttributesCache::for(StatusEnum::Active);
    expect($bag->label)->toBe('restored-label');

    // The corrupt entry didn't crash, didn't land in memo as a bag.
    $snapshot = AttributesCache::snapshot();
    expect($snapshot)->not->toHaveKey('corrupt::entry');
});
