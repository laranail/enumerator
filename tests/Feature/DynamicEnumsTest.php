<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Simtabi\Laranail\Enumerator\DynamicEnums\DatabaseBackedEnum;
use Simtabi\Laranail\Enumerator\Support\AttributesCache;
use Simtabi\Laranail\Enumerator\Support\CasesCache;

// DatabaseBackedEnum round-trip.

class TenantStatusFixture extends DatabaseBackedEnum
{
    protected static function table(): string
    {
        return 'tenant_status_fixtures';
    }
}

beforeEach(function (): void {
    // Fresh in-memory state for each test.
    CasesCache::flush();
    AttributesCache::flush();

    Schema::create('tenant_status_fixtures', function ($table): void {
        $table->id();
        $table->string('name');
        $table->string('value');
    });

    DB::table('tenant_status_fixtures')->insert([
        ['name' => 'ACTIVE', 'value' => 'active'],
        ['name' => 'SUSPENDED', 'value' => 'suspended'],
        ['name' => 'CHURNED', 'value' => 'churned'],
    ]);
});

afterEach(function (): void {
    Schema::dropIfExists('tenant_status_fixtures');
});

it('loadCases() reads the table and populates the cases cache', function (): void {
    TenantStatusFixture::loadCases();

    $cases = TenantStatusFixture::cases();
    expect($cases)->toHaveCount(3);
});

it('cases hydrated from DB respond to fromValue() / fromKey()', function (): void {
    TenantStatusFixture::loadCases();

    $active = TenantStatusFixture::fromValue('active');
    expect($active->getValue())->toBe('active');
    expect($active->getKey())->toBe('ACTIVE');

    $suspended = TenantStatusFixture::fromKey('SUSPENDED');
    expect($suspended->getValue())->toBe('suspended');
});

it('invokable case style works for DB-backed cases', function (): void {
    TenantStatusFixture::loadCases();

    $case = TenantStatusFixture::CHURNED();
    expect($case->getValue())->toBe('churned');
    expect($case->getKey())->toBe('CHURNED');
});

it('isValid() reflects the DB-loaded set', function (): void {
    TenantStatusFixture::loadCases();

    expect(TenantStatusFixture::isValid('active'))->toBeTrue();
    expect(TenantStatusFixture::isValid('nonexistent'))->toBeFalse();
});

it('reloadCases() picks up new DB rows mid-process', function (): void {
    TenantStatusFixture::loadCases();
    expect(TenantStatusFixture::cases())->toHaveCount(3);

    // Seed a new row.
    DB::table('tenant_status_fixtures')->insert([
        'name' => 'PILOT',
        'value' => 'pilot',
    ]);

    TenantStatusFixture::reloadCases();
    expect(TenantStatusFixture::cases())->toHaveCount(4);
    expect(TenantStatusFixture::PILOT()->getValue())->toBe('pilot');
});

it('labels() returns DB-sourced humanized labels (humanize-fallback)', function (): void {
    TenantStatusFixture::loadCases();

    $labels = TenantStatusFixture::labels();
    expect($labels['active'])->toBe('Active');
    expect($labels['suspended'])->toBe('Suspended');
});
