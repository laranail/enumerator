<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Support;

use Closure;
use Illuminate\Support\Facades\File;

/**
 * Two-layer cache: in-memory (per-request) over an optional file backend.
 * Driver selected via config('enumerator.cache.driver'):
 *
 *   - memory : memory only; cleared between requests
 *   - file   : file only; survives requests; warmed by `enumerator:cache`
 *   - layered: memory on read, file on miss; writes go to both
 *
 * The file backend uses a single PHP file (var_export-able payload) similar
 * to Laravel's bootstrap/cache/ pattern. Designed to be opcache-friendly.
 */
final class LayeredCache
{
    /** @var array<string, mixed> */
    private array $memory = [];

    /** @var array<string, mixed>|null */
    private ?array $file = null;

    private bool $fileLoaded = false;

    public function __construct(
        private readonly string $driver = 'layered',
        private readonly ?string $filePath = null,
    ) {}

    /**
     * Get a cached value; on miss, populate via $resolver, store, and return.
     *
     * @template T
     *
     * @param  Closure(): T  $resolver
     * @return T
     */
    public function remember(string $key, Closure $resolver): mixed
    {
        if ($this->driver === 'memory' || $this->driver === 'layered') {
            if (array_key_exists($key, $this->memory)) {
                return $this->memory[$key];
            }
        }

        if ($this->driver === 'file' || $this->driver === 'layered') {
            $this->ensureFileLoaded();
            if (is_array($this->file) && array_key_exists($key, $this->file)) {
                $value = $this->file[$key];
                if ($this->driver === 'layered') {
                    $this->memory[$key] = $value;
                }

                return $value;
            }
        }

        $value = $resolver();
        $this->put($key, $value);

        return $value;
    }

    public function put(string $key, mixed $value): void
    {
        if ($this->driver === 'memory' || $this->driver === 'layered') {
            $this->memory[$key] = $value;
        }

        if ($this->driver === 'file' || $this->driver === 'layered') {
            $this->ensureFileLoaded();
            $this->file ??= [];
            $this->file[$key] = $value;
        }
    }

    public function forget(string $key): void
    {
        unset($this->memory[$key]);
        if ($this->fileLoaded && is_array($this->file)) {
            unset($this->file[$key]);
        }
    }

    public function flush(): void
    {
        $this->memory = [];
        $this->file = null;
        $this->fileLoaded = false;
    }

    /**
     * Persist the current file cache to disk. Memory-only drivers no-op.
     */
    public function persist(): void
    {
        if ($this->driver === 'memory' || $this->filePath === null) {
            return;
        }

        $this->ensureFileLoaded();
        $payload = $this->file ?? [];
        File::ensureDirectoryExists(dirname($this->filePath), 0o755, true);
        $export = var_export($payload, true);
        File::put(
            $this->filePath,
            "<?php\n\ndeclare(strict_types=1);\n\nreturn {$export};\n",
            lock: true,
        );
    }

    /**
     * Delete the on-disk cache file. Memory-only drivers no-op.
     */
    public function clearFile(): void
    {
        if ($this->filePath !== null && File::exists($this->filePath)) {
            File::delete($this->filePath);
        }
        $this->file = null;
        $this->fileLoaded = false;
    }

    private function ensureFileLoaded(): void
    {
        if ($this->fileLoaded || $this->filePath === null) {
            return;
        }
        $this->fileLoaded = true;
        if (File::exists($this->filePath)) {
            /** @var mixed $payload */
            $payload = require $this->filePath;
            if (is_array($payload)) {
                $this->file = $payload;
            }
        }
    }

    public function driver(): string
    {
        return $this->driver;
    }

    public function filePath(): ?string
    {
        return $this->filePath;
    }
}
