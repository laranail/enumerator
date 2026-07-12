<?php

declare(strict_types=1);

use Simtabi\Laranail\Enumerator\Concerns\HasEnumAttributes;
use Simtabi\Laranail\Enumerator\Presets\Enums\StatusEnum;
use Simtabi\Laranail\Enumerator\Tests\Fixtures\Enums\LegacyStatusEnum;

// HasEnumAttributes — non-Eloquent host paths and AbstractEnumeratorClass
// projections. Feature tests cover the Eloquent-on-DB round-trip; these
// hit the property-fallback / class-const accessor branches.

it('default enumAttributes() returns an empty array (no predicates registered)', function (): void {
    $host = new class
    {
        use HasEnumAttributes;
    };

    // With empty config, predicates fall through to the BadMethodCall path.
    expect(fn () => $host->statusIs(StatusEnum::Active))
        ->toThrow(BadMethodCallException::class);
});

it('array-config form (enum + cast keys) resolves correctly', function (): void {
    $host = new class
    {
        use HasEnumAttributes;

        public ?StatusEnum $status = null;

        protected function enumAttributes(): array
        {
            return [
                'status' => ['enum' => StatusEnum::class],
            ];
        }
    };

    $host->status = StatusEnum::Pending;
    expect($host->status_label)->toBe('Pending');
});

it('property-fallback getEnumCase works on non-Eloquent hosts', function (): void {
    $host = new class
    {
        use HasEnumAttributes;

        public ?StatusEnum $status = null;

        protected function enumAttributes(): array
        {
            return ['status' => StatusEnum::class];
        }
    };

    $host->status = StatusEnum::Active;
    expect($host->status_label)->toBe('Active');
    expect($host->status_color)->toBe('success');
});

it('__get returns null for an unknown non-enum key on a non-Eloquent host', function (): void {
    $host = new class
    {
        use HasEnumAttributes;

        protected function enumAttributes(): array
        {
            return ['status' => StatusEnum::class];
        }
    };

    expect($host->unknown_attribute)->toBeNull();
});

it('__call throws BadMethodCallException for unknown method on a non-Eloquent host', function (): void {
    $host = new class
    {
        use HasEnumAttributes;

        protected function enumAttributes(): array
        {
            return ['status' => StatusEnum::class];
        }
    };

    $host->nonExistentMethod();
})->throws(BadMethodCallException::class);

it('projects _value for AbstractEnumeratorClass cases via getValue', function (): void {
    $host = new class
    {
        use HasEnumAttributes;

        public ?LegacyStatusEnum $kind = null;

        protected function enumAttributes(): array
        {
            return ['kind' => LegacyStatusEnum::class];
        }
    };

    $host->kind = LegacyStatusEnum::ACTIVE();
    expect($host->kind_value)->toBe('active');
});

it('projects _name for AbstractEnumeratorClass cases via getKey', function (): void {
    $host = new class
    {
        use HasEnumAttributes;

        public ?LegacyStatusEnum $kind = null;

        protected function enumAttributes(): array
        {
            return ['kind' => LegacyStatusEnum::class];
        }
    };

    $host->kind = LegacyStatusEnum::ACTIVE();
    expect($host->kind_name)->toBe('ACTIVE');
});

it('exposes the _help accessor (null for cases without #[Help])', function (): void {
    $host = new class
    {
        use HasEnumAttributes;

        public ?StatusEnum $status = null;

        protected function enumAttributes(): array
        {
            return ['status' => StatusEnum::class];
        }
    };

    $host->status = StatusEnum::Active;
    expect($host->status_help)->toBeNull();
});

it('initializeHasEnumAttributes() merges into a `$casts` property on non-Eloquent hosts', function (): void {
    $host = new class
    {
        use HasEnumAttributes;

        /** @var array<string, string> */
        public array $casts = ['legacy_field' => 'integer'];

        protected function enumAttributes(): array
        {
            return ['status' => StatusEnum::class];
        }
    };

    $host->initializeHasEnumAttributes();

    expect($host->casts)->toHaveKey('legacy_field');
    expect($host->casts)->toHaveKey('status');
    expect($host->casts['status'])->toContain('AsEnum');
});

it('predicate returns false when the case is null', function (): void {
    $host = new class
    {
        use HasEnumAttributes;

        public ?StatusEnum $status = null;

        protected function enumAttributes(): array
        {
            return ['status' => StatusEnum::class];
        }
    };

    expect($host->statusIs(StatusEnum::Active))->toBeFalse();
    expect($host->statusEquals(StatusEnum::Active))->toBeFalse();
});
