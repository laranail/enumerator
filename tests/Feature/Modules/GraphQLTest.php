<?php

declare(strict_types=1);

use Simtabi\Laranail\Enumerator\Modules\GraphQL\GraphQLServiceProvider;
use Simtabi\Laranail\Enumerator\Modules\GraphQL\SchemaExporter;
use Simtabi\Laranail\Enumerator\Presets\Enums\StatusEnum;
use Simtabi\Laranail\Enumerator\Tests\Fixtures\Enums\LegacyStatusEnum;

// framework-agnostic GraphQL schema export.

it('emits a portable .graphql enum fragment for native enums', function (): void {
    $schema = (new SchemaExporter)->export(StatusEnum::class);

    expect($schema)->toStartWith('enum StatusEnum {')
        ->toContain('  Active')
        ->toContain('  Inactive')
        ->toContain('  Pending')
        ->toContain('  Archived')
        ->toEndWith("}\n");
});

it('emits a fragment for class-const AbstractEnumeratorClass subclasses', function (): void {
    $schema = (new SchemaExporter)->export(LegacyStatusEnum::class);

    expect($schema)->toContain('enum LegacyStatusEnum {')
        ->toContain('  ACTIVE')
        ->toContain('  INACTIVE')
        ->toContain('  BANNED');
});

it('returns an empty string for non-enumerator classes', function (): void {
    $schema = (new SchemaExporter)->export(stdClass::class);

    expect($schema)->toBe('');
});

it('binds SchemaExporter as a singleton when the module is enabled', function (): void {
    config()->set('enumerator.modules.graphql', true);
    app()->register(GraphQLServiceProvider::class, true);

    expect(app(SchemaExporter::class))->toBeInstanceOf(SchemaExporter::class);
});
