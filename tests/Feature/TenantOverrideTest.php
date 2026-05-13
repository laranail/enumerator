<?php

declare(strict_types=1);

use Simtabi\Laranail\Enumerator\Contracts\TenantContext;
use Simtabi\Laranail\Enumerator\Presets\Enums\StatusEnum;
use Simtabi\Laranail\Enumerator\Support\NullTenantContext;

// per-tenant override layer.

it('default binding is NullTenantContext (preserves single-tenant behaviour)', function (): void {
    $bound = app(TenantContext::class);

    expect($bound)->toBeInstanceOf(NullTenantContext::class);
    expect($bound->currentTenant())->toBeNull();
    expect($bound->overridesFor(StatusEnum::class))->toBe([]);
});

it('config-driven driver swap binds the consumer-provided implementation', function (): void {
    $fake = new class implements TenantContext
    {
        public function currentTenant(): null|string|int
        {
            return 'tenant-42';
        }

        public function overridesFor(string $enumClass): array
        {
            return [
                'Active' => ['color' => 'tenant-purple'],
            ];
        }
    };

    app()->instance(TenantContext::class, $fake);

    // Override resolution: tenant context first, config fallback, attribute default.
    expect(StatusEnum::Active->color())->toBe('tenant-purple');

    // Inactive has no tenant override → falls through to attribute (#[Color('ghost')]).
    expect(StatusEnum::Inactive->color())->toBe('ghost');
});

it('tenant overrides take precedence over config overrides', function (): void {
    config()->set('enumerator.overrides', [
        StatusEnum::class => [
            'Active' => ['color' => 'config-red'],
        ],
    ]);

    $fake = new class implements TenantContext
    {
        public function currentTenant(): null|string|int
        {
            return 't1';
        }

        public function overridesFor(string $enumClass): array
        {
            return ['Active' => ['color' => 'tenant-blue']];
        }
    };

    app()->instance(TenantContext::class, $fake);

    expect(StatusEnum::Active->color())->toBe('tenant-blue');
});

it('empty tenant overrides fall through to config overrides', function (): void {
    config()->set('enumerator.overrides', [
        StatusEnum::class => [
            'Active' => ['color' => 'config-red'],
        ],
    ]);

    app()->instance(TenantContext::class, new NullTenantContext);

    expect(StatusEnum::Active->color())->toBe('config-red');
});
