<?php

declare(strict_types=1);

use Simtabi\Laranail\Enumerator\Modules\OpenApi\OpenApiSchemaExporter;
use Simtabi\Laranail\Enumerator\Presets\Enums\StatusEnum;
use Simtabi\Laranail\Enumerator\Tests\Fixtures\Enums\BackedIntStatusEnum;
use Simtabi\Laranail\Enumerator\Tests\Fixtures\Enums\LegacyStatusEnum;
use Simtabi\Laranail\Enumerator\Tests\Fixtures\Enums\PureColorEnum;

// OpenApiSchemaExporter — emits OpenAPI 3.1 component-schema fragments.

it('export() returns the canonical shape for a string-backed enum', function (): void {
    $schema = (new OpenApiSchemaExporter)->export(StatusEnum::class);

    expect($schema)->toHaveKey('type')
        ->toHaveKey('enum')
        ->toHaveKey('x-enum-varnames');
    expect($schema['type'])->toBe('string');
    expect($schema['enum'])->toBe(['active', 'inactive', 'pending', 'archived']);
    expect($schema['x-enum-varnames'])->toBe(['Active', 'Inactive', 'Pending', 'Archived']);
});

it('export() emits integer type for int-backed enums', function (): void {
    $schema = (new OpenApiSchemaExporter)->export(BackedIntStatusEnum::class);
    expect($schema['type'])->toBe('integer');
    expect($schema['enum'])->toContain(1);
});

it('export() emits string type for pure enums', function (): void {
    $schema = (new OpenApiSchemaExporter)->export(PureColorEnum::class);
    expect($schema['type'])->toBe('string');
    expect($schema['enum'])->toBe(['Red', 'Green', 'Blue']);
});

it('export() returns the empty shape for a non-enum class', function (): void {
    $schema = (new OpenApiSchemaExporter)->export(stdClass::class);
    expect($schema['enum'])->toBe([]);
    expect($schema['x-enum-varnames'])->toBe([]);
});

it('export() includes x-enum-descriptions only when at least one description is set', function (): void {
    // StatusEnum has no descriptions, so the field should be omitted.
    $schema = (new OpenApiSchemaExporter)->export(StatusEnum::class);
    expect($schema)->not->toHaveKey('x-enum-descriptions');
});

it('export() handles class-const enums via getKey/getValue', function (): void {
    $schema = (new OpenApiSchemaExporter)->export(LegacyStatusEnum::class);
    expect($schema['type'])->toBe('string');
    expect($schema['enum'])->toContain('active');
});
