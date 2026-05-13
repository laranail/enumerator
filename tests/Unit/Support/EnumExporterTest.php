<?php

declare(strict_types=1);

use Simtabi\Laranail\Enumerator\Presets\Enums\StatusEnum;
use Simtabi\Laranail\Enumerator\Support\EnumExporter;

// EnumExporter — array / JSON / PHP file / TypeScript emitters.

it('toArray() returns one descriptor per case with value/name/label', function (): void {
    $rows = (new EnumExporter)->toArray(StatusEnum::class);
    expect($rows)->toHaveCount(4);

    $first = $rows[0];
    expect($first)->toHaveKey('value')->toHaveKey('name')->toHaveKey('label');
    expect($first['name'])->toBe('Active');
    expect($first['value'])->toBe('active');
    expect($first['label'])->toBe('Active');
});

it('toJson() produces parseable JSON of the array form', function (): void {
    $json = (new EnumExporter)->toJson(StatusEnum::class);
    $decoded = json_decode($json, true);
    expect($decoded)->toBeArray();
    expect($decoded)->toHaveCount(4);
});

it('toJson() respects custom flags', function (): void {
    $json = (new EnumExporter)->toJson(StatusEnum::class, 0);
    expect($json)->not->toContain("\n");
});

it('toPhpFile() emits a parseable PHP return file', function (): void {
    $php = (new EnumExporter)->toPhpFile(StatusEnum::class);
    expect($php)->toStartWith('<?php');
    expect($php)->toContain('declare(strict_types=1);');
    expect($php)->toContain('return');

    // Eval-execute the body to confirm it parses.
    $tmp = tempnam(sys_get_temp_dir(), 'enum-export-');
    file_put_contents($tmp, $php);
    $payload = require $tmp;
    unlink($tmp);

    expect($payload)->toBeArray()->toHaveCount(4);
});

it('toTypeScript() emits a const object, union type, and values tuple', function (): void {
    $ts = (new EnumExporter)->toTypeScript(StatusEnum::class);

    expect($ts)->toContain('export const StatusEnum = {');
    expect($ts)->toContain("Active: 'active'");
    expect($ts)->toContain('export type StatusEnumValue =');
    expect($ts)->toContain("'active' | 'inactive' | 'pending' | 'archived'");
    expect($ts)->toContain('export const StatusEnum_VALUES = [');
    expect($ts)->toContain('as const;');
});
