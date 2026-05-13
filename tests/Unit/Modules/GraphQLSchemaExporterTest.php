<?php

declare(strict_types=1);

use Simtabi\Laranail\Enumerator\Modules\GraphQL\SchemaExporter;
use Simtabi\Laranail\Enumerator\Presets\Enums\StatusEnum;
use Simtabi\Laranail\Enumerator\Tests\Fixtures\Enums\LegacyStatusEnum;
use Simtabi\Laranail\Enumerator\Tests\Fixtures\Enums\PureColorEnum;

// SchemaExporter — portable .graphql enum fragment emitter.

it('export() returns the enum block with the short class name', function (): void {
    $graphql = (new SchemaExporter)->export(StatusEnum::class);
    expect($graphql)->toContain('enum StatusEnum {');
    expect($graphql)->toContain('  Active');
    expect($graphql)->toContain('  Inactive');
    expect($graphql)->toContain('  Pending');
    expect($graphql)->toContain('  Archived');
    expect($graphql)->toEndWith("}\n");
});

it('export() returns an empty string for a non-enum class', function (): void {
    expect((new SchemaExporter)->export(stdClass::class))->toBe('');
});

it('export() works for pure enums', function (): void {
    $graphql = (new SchemaExporter)->export(PureColorEnum::class);
    expect($graphql)->toContain('enum PureColorEnum {');
    expect($graphql)->toContain('  Red');
    expect($graphql)->toContain('  Green');
    expect($graphql)->toContain('  Blue');
});

it('export() uses getKey for class-const enums', function (): void {
    $graphql = (new SchemaExporter)->export(LegacyStatusEnum::class);
    expect($graphql)->toContain('enum LegacyStatusEnum {');
    expect($graphql)->toContain('  ACTIVE');
});

it('export() omits the class-level description block when none is set', function (): void {
    $graphql = (new SchemaExporter)->export(StatusEnum::class);
    expect($graphql)->toStartWith('enum StatusEnum {');
});
