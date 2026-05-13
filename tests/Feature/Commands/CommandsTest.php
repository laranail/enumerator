<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Simtabi\Laranail\Enumerator\Presets\Enums\StatusEnum;

// Console commands — Artisan-runnable behaviour smoke tests.

afterEach(function (): void {
    foreach (glob(sys_get_temp_dir() . '/enum-export-test-*') ?: [] as $f) {
        @unlink($f);
    }
});

// enumerator:cache + enumerator:cache:clear

it('enumerator:cache returns SUCCESS', function (): void {
    config()->set('enumerator.cache.auto_warm_classes', [StatusEnum::class]);
    $exit = Artisan::call('enumerator:cache');
    expect($exit)->toBe(0);
});

it('enumerator:cache:clear returns SUCCESS', function (): void {
    $exit = Artisan::call('enumerator:cache:clear');
    expect($exit)->toBe(0);
});

// enumerator:export

it('enumerator:export emits JSON to stdout by default', function (): void {
    Artisan::call('enumerator:export', ['class' => StatusEnum::class]);
    $output = Artisan::output();
    expect($output)->toContain('"active"');
});

it('enumerator:export --ts emits TypeScript', function (): void {
    Artisan::call('enumerator:export', [
        'class' => StatusEnum::class,
        '--ts' => true,
    ]);
    $output = Artisan::output();
    expect($output)->toContain('export const StatusEnum = {');
});

it('enumerator:export --php emits a PHP return file', function (): void {
    Artisan::call('enumerator:export', [
        'class' => StatusEnum::class,
        '--php' => true,
    ]);
    $output = Artisan::output();
    expect($output)->toContain('<?php');
    expect($output)->toContain('declare(strict_types=1);');
});

it('enumerator:export --out writes to disk', function (): void {
    $path = sys_get_temp_dir() . '/enum-export-test-' . bin2hex(random_bytes(4)) . '.json';
    Artisan::call('enumerator:export', [
        'class' => StatusEnum::class,
        '--out' => $path,
    ]);
    expect(File::exists($path))->toBeTrue();
    expect(File::get($path))->toContain('"active"');
});

it('enumerator:export fails when the class is not an enum', function (): void {
    $exit = Artisan::call('enumerator:export', ['class' => stdClass::class]);
    expect($exit)->toBe(1);
});

// enumerator:annotate

it('enumerator:annotate runs without error for a valid enum', function (): void {
    $exit = Artisan::call('enumerator:annotate', ['class' => StatusEnum::class]);
    expect($exit)->toBe(0);
});

// enumerator:ide-helper

it('enumerator:ide-helper runs and emits to a temp output', function (): void {
    // Use a path that's resolvable inside Testbench's app root.
    $path = 'enum-export-test-' . bin2hex(random_bytes(4)) . '.php';
    config()->set('enumerator.ide_helper.classes', [StatusEnum::class]);

    $exit = Artisan::call('enumerator:ide-helper', ['--out' => $path]);
    expect($exit)->toBe(0);

    @unlink(app()->basePath($path));
});
