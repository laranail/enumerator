<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;
use Simtabi\Laranail\Enumerator\Support\LayeredCache;

// LayeredCache — memory + file two-tier cache.

function layeredCachePath(): string
{
    return sys_get_temp_dir() . '/enumerator-layered-' . bin2hex(random_bytes(4)) . '.php';
}

afterEach(function (): void {
    // Cleanup any orphan caches from each test.
    foreach (glob(sys_get_temp_dir() . '/enumerator-layered-*.php') ?: [] as $f) {
        @unlink($f);
    }
});

it('driver() and filePath() expose constructor args', function (): void {
    $path = layeredCachePath();
    $cache = new LayeredCache('layered', $path);
    expect($cache->driver())->toBe('layered');
    expect($cache->filePath())->toBe($path);
});

it('remember() resolves once and caches the result (memory driver)', function (): void {
    $cache = new LayeredCache('memory');
    $calls = 0;
    $resolver = function () use (&$calls): string {
        $calls++;

        return 'value';
    };
    expect($cache->remember('k1', $resolver))->toBe('value');
    expect($cache->remember('k1', $resolver))->toBe('value');
    expect($calls)->toBe(1);
});

it('put() stores a value retrievable by remember()', function (): void {
    $cache = new LayeredCache('memory');
    $cache->put('explicit', ['foo' => 'bar']);
    $hit = $cache->remember('explicit', fn (): array => ['should-not-fire']);
    expect($hit)->toBe(['foo' => 'bar']);
});

it('forget() removes a single key', function (): void {
    $cache = new LayeredCache('memory');
    $cache->put('keep', 1);
    $cache->put('drop', 2);
    $cache->forget('drop');

    $calls = 0;
    $cache->remember('drop', function () use (&$calls): int {
        $calls++;

        return 99;
    });
    expect($calls)->toBe(1);

    // 'keep' still cached.
    $cache->remember('keep', function () use (&$calls): int {
        $calls++;

        return 100;
    });
    expect($calls)->toBe(1);
});

it('flush() clears everything', function (): void {
    $cache = new LayeredCache('memory');
    $cache->put('a', 1);
    $cache->put('b', 2);
    $cache->flush();

    $calls = 0;
    $cache->remember('a', function () use (&$calls): int {
        $calls++;

        return 0;
    });
    expect($calls)->toBe(1);
});

it('layered driver persists across instances via the file backend', function (): void {
    $path = layeredCachePath();

    $writer = new LayeredCache('layered', $path);
    $writer->put('shared', 'hello');
    $writer->persist();
    expect(File::exists($path))->toBeTrue();

    $reader = new LayeredCache('layered', $path);
    $hit = $reader->remember('shared', fn (): string => 'fallback');
    expect($hit)->toBe('hello');
});

it('persist() no-ops for the memory driver', function (): void {
    $cache = new LayeredCache('memory');
    $cache->put('x', 'y');
    $cache->persist();
    // Nothing to assert beyond "no exception" — memory driver has no path.
    expect(true)->toBeTrue();
});

it('clearFile() removes the on-disk cache', function (): void {
    $path = layeredCachePath();
    $cache = new LayeredCache('layered', $path);
    $cache->put('x', 1);
    $cache->persist();
    expect(File::exists($path))->toBeTrue();

    $cache->clearFile();
    expect(File::exists($path))->toBeFalse();
});

it('file driver loads existing payloads on remember()', function (): void {
    $path = layeredCachePath();
    File::ensureDirectoryExists(dirname($path));
    File::put($path, "<?php\n\nreturn ['precomputed' => 42];\n");

    $cache = new LayeredCache('file', $path);
    $hit = $cache->remember('precomputed', fn (): int => 0);
    expect($hit)->toBe(42);
});
