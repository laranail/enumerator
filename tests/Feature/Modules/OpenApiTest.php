<?php

declare(strict_types=1);

use Simtabi\Laranail\Enumerator\Modules\OpenApi\OpenApiSchemaExporter;
use Simtabi\Laranail\Enumerator\Modules\OpenApi\OpenApiServiceProvider;
use Simtabi\Laranail\Enumerator\Presets\Enums\StatusEnum;
use Simtabi\Laranail\Enumerator\Tests\Fixtures\Enums\BackedIntStatusEnum;
use Simtabi\Laranail\Enumerator\Tests\Fixtures\Enums\LegacyStatusEnum;
use Simtabi\Laranail\Enumerator\Tests\Fixtures\Enums\PureColorEnum;

// OpenAPI 3.1 component schema export.

it('emits an OpenAPI 3.1 schema for string-backed enums', function (): void {
    $schema = (new OpenApiSchemaExporter)->export(StatusEnum::class);

    expect($schema['type'])->toBe('string');
    expect($schema['enum'])->toBe(['active', 'inactive', 'pending', 'archived']);
    expect($schema['x-enum-varnames'])->toBe(['Active', 'Inactive', 'Pending', 'Archived']);
});

it('emits "integer" type for int-backed enums', function (): void {
    $schema = (new OpenApiSchemaExporter)->export(BackedIntStatusEnum::class);

    expect($schema['type'])->toBe('integer');
    expect($schema['enum'])->toBe([1, 2, 3]);
    expect($schema['x-enum-varnames'])->toBe(['Active', 'Inactive', 'Pending']);
});

it('emits "string" type for pure enums (no backing)', function (): void {
    $schema = (new OpenApiSchemaExporter)->export(PureColorEnum::class);

    expect($schema['type'])->toBe('string');
    expect($schema['enum'])->toBe(['Red', 'Green', 'Blue']);
});

it('emits "string" type for class-const AbstractEnumeratorClass subclasses', function (): void {
    $schema = (new OpenApiSchemaExporter)->export(LegacyStatusEnum::class);

    expect($schema['type'])->toBe('string');
    expect($schema['enum'])->toBe(['active', 'inactive', 'banned']);
    expect($schema['x-enum-varnames'])->toBe(['ACTIVE', 'INACTIVE', 'BANNED']);
});

it('omits x-enum-descriptions when no cases have one', function (): void {
    $schema = (new OpenApiSchemaExporter)->export(StatusEnum::class);

    expect($schema)->not->toHaveKey('x-enum-descriptions');
});

it('returns an empty schema for non-enumerator classes', function (): void {
    $schema = (new OpenApiSchemaExporter)->export(stdClass::class);

    expect($schema['type'])->toBe('string');
    expect($schema['enum'])->toBe([]);
    expect($schema['x-enum-varnames'])->toBe([]);
});

it('binds OpenApiSchemaExporter as a singleton when the module is enabled', function (): void {
    config()->set('enumerator.modules.openapi', true);
    // Re-register the module to pick up the new config.
    app()->register(OpenApiServiceProvider::class, true);

    expect(app(OpenApiSchemaExporter::class))->toBeInstanceOf(OpenApiSchemaExporter::class);
});
