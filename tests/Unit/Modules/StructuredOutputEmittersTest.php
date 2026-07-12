<?php

declare(strict_types=1);

use Simtabi\Laranail\Enumerator\Modules\StructuredOutput\AnthropicSchemaEmitter;
use Simtabi\Laranail\Enumerator\Modules\StructuredOutput\McpSchemaEmitter;
use Simtabi\Laranail\Enumerator\Modules\StructuredOutput\OpenAiSchemaEmitter;
use Simtabi\Laranail\Enumerator\Presets\Enums\StatusEnum;
use Simtabi\Laranail\Enumerator\Tests\Fixtures\Enums\BackedIntStatusEnum;

// StructuredOutput emitters — three sibling JSON-schema producers for
// OpenAI / Anthropic / MCP. All return arrays; no AI-SDK dependency.

// OpenAI

it('OpenAiSchemaEmitter asToolParameter() returns type+enum for backed string enum', function (): void {
    $schema = (new OpenAiSchemaEmitter)->asToolParameter(StatusEnum::class);
    expect($schema)->toHaveKey('type')->toHaveKey('enum');
    expect($schema['type'])->toBe('string');
    expect($schema['enum'])->toBe(['active', 'inactive', 'pending', 'archived']);
});

it('OpenAiSchemaEmitter asToolParameter() returns integer type for int-backed enum', function (): void {
    $schema = (new OpenAiSchemaEmitter)->asToolParameter(BackedIntStatusEnum::class);
    expect($schema['type'])->toBe('integer');
});

it('OpenAiSchemaEmitter asToolParameter() returns empty enum for non-enumerator class', function (): void {
    $schema = (new OpenAiSchemaEmitter)->asToolParameter(stdClass::class);
    expect($schema['type'])->toBe('string');
    expect($schema['enum'])->toBe([]);
});

it('OpenAiSchemaEmitter asResponseFormat() wraps the schema with the json_schema envelope', function (): void {
    $payload = (new OpenAiSchemaEmitter)->asResponseFormat(StatusEnum::class);
    expect($payload['type'])->toBe('json_schema');
    expect($payload['json_schema'])->toHaveKey('name')
        ->toHaveKey('strict')
        ->toHaveKey('schema');
    expect($payload['json_schema']['name'])->toBe('StatusEnum');
    expect($payload['json_schema']['strict'])->toBeTrue();
    expect($payload['json_schema']['schema']['enum'])->toContain('active');
});

// Anthropic

it('AnthropicSchemaEmitter asToolInputProperty() returns a JSON-schema property fragment', function (): void {
    $schema = (new AnthropicSchemaEmitter)->asToolInputProperty(StatusEnum::class);
    expect($schema)->toHaveKey('type')->toHaveKey('enum');
    expect($schema['type'])->toBe('string');
    expect($schema['enum'])->toContain('active');
});

it('AnthropicSchemaEmitter asToolInputProperty() handles int-backed enums', function (): void {
    $schema = (new AnthropicSchemaEmitter)->asToolInputProperty(BackedIntStatusEnum::class);
    expect($schema['type'])->toBe('integer');
});

// MCP

it('McpSchemaEmitter asToolInputProperty() returns the JSON-schema fragment', function (): void {
    $schema = (new McpSchemaEmitter)->asToolInputProperty(StatusEnum::class);
    expect($schema)->toHaveKey('type')->toHaveKey('enum');
    expect($schema['enum'])->toContain('active');
});

it('McpSchemaEmitter asResourceParameter() returns the resource-parameter shape', function (): void {
    $schema = (new McpSchemaEmitter)->asResourceParameter(StatusEnum::class);
    expect($schema)->toHaveKey('type')->toHaveKey('enum');
});
