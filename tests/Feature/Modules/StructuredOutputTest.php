<?php

declare(strict_types=1);

use Simtabi\Laranail\Enumerator\Modules\StructuredOutput\AnthropicSchemaEmitter;
use Simtabi\Laranail\Enumerator\Modules\StructuredOutput\McpSchemaEmitter;
use Simtabi\Laranail\Enumerator\Modules\StructuredOutput\OpenAiSchemaEmitter;
use Simtabi\Laranail\Enumerator\Modules\StructuredOutput\StructuredOutputServiceProvider;
use Simtabi\Laranail\Enumerator\Presets\Enums\StatusEnum;
use Simtabi\Laranail\Enumerator\Tests\Fixtures\Enums\BackedIntStatusEnum;

it('OpenAI response_format wraps the schema correctly', function (): void {
    $schema = (new OpenAiSchemaEmitter)->asResponseFormat(StatusEnum::class);

    expect($schema['type'])->toBe('json_schema');
    expect($schema['json_schema']['name'])->toBe('StatusEnum');
    expect($schema['json_schema']['strict'])->toBeTrue();
    expect($schema['json_schema']['schema']['type'])->toBe('string');
    expect($schema['json_schema']['schema']['enum'])->toBe(['active', 'inactive', 'pending', 'archived']);
});

it('OpenAI tool parameter is a plain JSON Schema fragment', function (): void {
    $schema = (new OpenAiSchemaEmitter)->asToolParameter(BackedIntStatusEnum::class);

    expect($schema['type'])->toBe('integer');
    expect($schema['enum'])->toBe([1, 2, 3]);
});

it('Anthropic tool input property uses standard JSON Schema', function (): void {
    $schema = (new AnthropicSchemaEmitter)->asToolInputProperty(StatusEnum::class);

    expect($schema['type'])->toBe('string');
    expect($schema['enum'])->toBe(['active', 'inactive', 'pending', 'archived']);
});

it('MCP tool input property includes a title and standard JSON Schema fields', function (): void {
    $schema = (new McpSchemaEmitter)->asToolInputProperty(StatusEnum::class);

    expect($schema['type'])->toBe('string');
    expect($schema['enum'])->toBe(['active', 'inactive', 'pending', 'archived']);
    expect($schema['title'])->toBe('StatusEnum');
});

it('MCP resource parameter mirrors the tool input property shape', function (): void {
    $tool = (new McpSchemaEmitter)->asToolInputProperty(StatusEnum::class);
    $resource = (new McpSchemaEmitter)->asResourceParameter(StatusEnum::class);

    expect($resource)->toBe($tool);
});

it('all three emitters return empty schemas for non-enumerator classes', function (): void {
    expect((new OpenAiSchemaEmitter)->asToolParameter(stdClass::class)['enum'])->toBe([]);
    expect((new AnthropicSchemaEmitter)->asToolInputProperty(stdClass::class)['enum'])->toBe([]);
    expect((new McpSchemaEmitter)->asToolInputProperty(stdClass::class)['enum'])->toBe([]);
});

it('binds all three emitters as singletons when module is enabled', function (): void {
    config()->set('enumerator.modules.structured_output', true);
    app()->register(StructuredOutputServiceProvider::class, true);

    expect(app(OpenAiSchemaEmitter::class))->toBeInstanceOf(OpenAiSchemaEmitter::class);
    expect(app(AnthropicSchemaEmitter::class))->toBeInstanceOf(AnthropicSchemaEmitter::class);
    expect(app(McpSchemaEmitter::class))->toBeInstanceOf(McpSchemaEmitter::class);
});
