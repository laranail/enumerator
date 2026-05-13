<?php

declare(strict_types=1);

use Simtabi\Laranail\Enumerator\Presets\Enums\StatusEnum;
use Simtabi\Laranail\Enumerator\Support\CasesCache;
use Simtabi\Laranail\Enumerator\Tests\Fixtures\Enums\LegacyStatusEnum;

// CasesCache — reflection cache for native + class-const enum cases.

beforeEach(function (): void {
    CasesCache::flush();
});

it('nativeCases() returns the case list', function (): void {
    $cases = CasesCache::nativeCases(StatusEnum::class);
    expect($cases)->toBe(StatusEnum::cases());
});

it('nativeCases() memoises the result', function (): void {
    $first = CasesCache::nativeCases(StatusEnum::class);
    $second = CasesCache::nativeCases(StatusEnum::class);
    expect($first)->toBe($second);
});

it('classConstants() returns [name => value] for a class-const enum', function (): void {
    $constants = CasesCache::classConstants(LegacyStatusEnum::class);
    expect($constants)->toHaveKey('ACTIVE')
        ->toHaveKey('INACTIVE')
        ->toHaveKey('BANNED');
    expect($constants['ACTIVE'])->toBe('active');
});

it('classConstants() memoises the result', function (): void {
    $first = CasesCache::classConstants(LegacyStatusEnum::class);
    $second = CasesCache::classConstants(LegacyStatusEnum::class);
    expect($first)->toBe($second);
});

it('reflectConstant() returns a ReflectionClassConstant', function (): void {
    $ref = CasesCache::reflectConstant(LegacyStatusEnum::class, 'ACTIVE');
    expect($ref->getName())->toBe('ACTIVE');
    expect($ref->getValue())->toBe('active');
});

it('setConstants() injects a constants map bypassing reflection', function (): void {
    CasesCache::setConstants('App\\Imaginary\\Enum', ['FOO' => 'foo', 'BAR' => 'bar']);

    $constants = CasesCache::classConstants('App\\Imaginary\\Enum');
    expect($constants)->toBe(['FOO' => 'foo', 'BAR' => 'bar']);
});

it('flush() clears the memo', function (): void {
    CasesCache::nativeCases(StatusEnum::class);
    expect(CasesCache::snapshot())->toHaveKey(StatusEnum::class);
    CasesCache::flush();
    expect(CasesCache::snapshot())->toBe([]);
});

it('snapshot() and restore() round-trip the memo', function (): void {
    CasesCache::nativeCases(StatusEnum::class);
    $snapshot = CasesCache::snapshot();
    expect($snapshot)->toHaveKey(StatusEnum::class);

    CasesCache::flush();
    expect(CasesCache::snapshot())->toBe([]);

    CasesCache::restore($snapshot);
    expect(CasesCache::snapshot())->toHaveKey(StatusEnum::class);
});
