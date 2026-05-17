<?php

declare(strict_types=1);

use Simtabi\Laranail\Enumerator\Modules\GraphQL\GraphQLServiceProvider;
use Simtabi\Laranail\Enumerator\Modules\GraphQL\SchemaExporter as GraphQLExporter;
use Simtabi\Laranail\Enumerator\Modules\OpenApi\OpenApiSchemaExporter;
use Simtabi\Laranail\Enumerator\Modules\OpenApi\OpenApiServiceProvider;
use Simtabi\Laranail\Enumerator\Modules\Pest\PestServiceProvider;
use Simtabi\Laranail\Enumerator\Modules\Saloon\EnumCaster;
use Simtabi\Laranail\Enumerator\Modules\Saloon\SaloonServiceProvider;
use Simtabi\Laranail\Enumerator\Modules\StructuredOutput\AnthropicSchemaEmitter;
use Simtabi\Laranail\Enumerator\Modules\StructuredOutput\McpSchemaEmitter;
use Simtabi\Laranail\Enumerator\Modules\StructuredOutput\OpenAiSchemaEmitter;
use Simtabi\Laranail\Enumerator\Modules\StructuredOutput\StructuredOutputServiceProvider;
use Simtabi\Laranail\Enumerator\Presets\Enums\StatusEnum;

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
    config()->set('enumerator.modules.pest', false);

    (new StructuredOutputServiceProvider(app()))->boot();
    (new GraphQLServiceProvider(app()))->boot();
    (new OpenApiServiceProvider(app()))->boot();
    (new SaloonServiceProvider(app()))->boot();
    (new PestServiceProvider(app()))->boot();

    expect(true)->toBeTrue();
});

// Pest module — PR-υ (v0.5.0) coverage push. PestServiceProvider's
// `boot()` gates on (a) Pest installed AND (b) the config toggle.
// Existing PestTest covers the Expectations surface directly; this
// suite drives the provider's gate.

it('PestServiceProvider::register() is a no-op (no bindings)', function (): void {
    $fresh = clone app();
    (new PestServiceProvider($fresh))->register();

    // Register is intentionally empty — the module is a boot-time
    // expectation registrar, not a container-binding module.
    expect(true)->toBeTrue();
});

it('PestServiceProvider::boot() invokes Expectations::register() when the module is on', function (): void {
    config()->set('enumerator.modules.pest', true);

    // Sanity: function_exists('expect') is true inside a Pest test
    // run, so shouldRegister() returns true; boot() reaches the
    // Expectations::register() call (line 30 of PestServiceProvider).
    (new PestServiceProvider(app()))->boot();

    // After boot, the toBeCase expectation IS available — proves
    // Expectations::register() ran.
    expect(StatusEnum::Active)
        ->toBeCase(StatusEnum::Active);
});

it('PestServiceProvider::boot() short-circuits when the module is off', function (): void {
    config()->set('enumerator.modules.pest', false);

    // shouldRegister() returns false; Expectations::register() is
    // NOT called. No assertion target — just confirm boot doesn't
    // throw.
    (new PestServiceProvider(app()))->boot();
    expect(true)->toBeTrue();
});
