<?php

declare(strict_types=1);

use Simtabi\Laranail\Enumerator\Modules\GraphQL\GraphQLServiceProvider;
use Simtabi\Laranail\Enumerator\Modules\GraphQL\SchemaExporter as GraphQLExporter;
use Simtabi\Laranail\Enumerator\Modules\OpenApi\OpenApiSchemaExporter;
use Simtabi\Laranail\Enumerator\Modules\OpenApi\OpenApiServiceProvider;
use Simtabi\Laranail\Enumerator\Modules\Saloon\EnumCaster;
use Simtabi\Laranail\Enumerator\Modules\Saloon\SaloonServiceProvider;
use Simtabi\Laranail\Enumerator\Modules\StructuredOutput\AnthropicSchemaEmitter;
use Simtabi\Laranail\Enumerator\Modules\StructuredOutput\McpSchemaEmitter;
use Simtabi\Laranail\Enumerator\Modules\StructuredOutput\OpenAiSchemaEmitter;
use Simtabi\Laranail\Enumerator\Modules\StructuredOutput\StructuredOutputServiceProvider;

// Module service providers — gating + container-binding behaviour.

// StructuredOutput

it('StructuredOutputServiceProvider does not bind when the module is off', function (): void {
    config()->set('enumerator.modules.structured_output', false);
    $fresh = clone app();
    (new StructuredOutputServiceProvider($fresh))->register();
    expect($fresh->bound(OpenAiSchemaEmitter::class))->toBeFalse();
});

it('StructuredOutputServiceProvider binds all three emitters when on', function (): void {
    config()->set('enumerator.modules.structured_output', true);
    (new StructuredOutputServiceProvider(app()))->register();
    expect(app(OpenAiSchemaEmitter::class))->toBeInstanceOf(OpenAiSchemaEmitter::class);
    expect(app(AnthropicSchemaEmitter::class))->toBeInstanceOf(AnthropicSchemaEmitter::class);
    expect(app(McpSchemaEmitter::class))->toBeInstanceOf(McpSchemaEmitter::class);
});

// GraphQL

it('GraphQLServiceProvider does not bind when off', function (): void {
    config()->set('enumerator.modules.graphql', false);
    $fresh = clone app();
    (new GraphQLServiceProvider($fresh))->register();
    expect($fresh->bound(GraphQLExporter::class))->toBeFalse();
});

it('GraphQLServiceProvider binds SchemaExporter when on', function (): void {
    config()->set('enumerator.modules.graphql', true);
    (new GraphQLServiceProvider(app()))->register();
    expect(app(GraphQLExporter::class))->toBeInstanceOf(GraphQLExporter::class);
});

// OpenAPI

it('OpenApiServiceProvider does not bind when off', function (): void {
    config()->set('enumerator.modules.openapi', false);
    $fresh = clone app();
    (new OpenApiServiceProvider($fresh))->register();
    expect($fresh->bound(OpenApiSchemaExporter::class))->toBeFalse();
});

it('OpenApiServiceProvider binds OpenApiSchemaExporter when on', function (): void {
    config()->set('enumerator.modules.openapi', true);
    (new OpenApiServiceProvider(app()))->register();
    expect(app(OpenApiSchemaExporter::class))->toBeInstanceOf(OpenApiSchemaExporter::class);
});

// Saloon

it('SaloonServiceProvider does not bind when off', function (): void {
    config()->set('enumerator.modules.saloon', false);
    $fresh = clone app();
    (new SaloonServiceProvider($fresh))->register();
    expect($fresh->bound(EnumCaster::class))->toBeFalse();
});

it('SaloonServiceProvider binds EnumCaster when on', function (): void {
    config()->set('enumerator.modules.saloon', true);
    (new SaloonServiceProvider(app()))->register();
    expect(app(EnumCaster::class))->toBeInstanceOf(EnumCaster::class);
});

// All providers should boot() without error in default config

it('every module provider boot()s cleanly when its toggle is off', function (): void {
    config()->set('enumerator.modules.structured_output', false);
    config()->set('enumerator.modules.graphql', false);
    config()->set('enumerator.modules.openapi', false);
    config()->set('enumerator.modules.saloon', false);

    (new StructuredOutputServiceProvider(app()))->boot();
    (new GraphQLServiceProvider(app()))->boot();
    (new OpenApiServiceProvider(app()))->boot();
    (new SaloonServiceProvider(app()))->boot();

    expect(true)->toBeTrue();
});
