<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Simtabi\Laranail\Enumerator\Console\Concerns\SupportsNamespacedNames;
use Simtabi\Laranail\Enumerator\Support\EnumExporter;

class ExportEnumeratorCommand extends Command
{
    use SupportsNamespacedNames;

    protected $signature = 'laranail::enumerator.export {class : Fully-qualified enum class} {--ts : Emit TypeScript} {--json : Emit JSON} {--php : Emit a PHP array file} {--out= : Output file path; defaults to stdout}';

    protected $description = 'Export an enum to TS / JSON / PHP for frontend or downstream consumption.';

    /** @var list<string> */
    protected array $commandAliases = ['enumerator:export'];

    public function handle(EnumExporter $exporter): int
    {
        $class = (string) $this->argument('class');
        if (! enum_exists($class)) {
            $this->error("Class {$class} is not an enum.");

            return self::FAILURE;
        }

        $payload = match (true) {
            (bool) $this->option('ts') => $exporter->toTypeScript($class),
            (bool) $this->option('php') => $exporter->toPhpFile($class),
            default => $exporter->toJson($class),
        };

        $out = (string) ($this->option('out') ?? '');
        if ($out === '') {
            $this->line($payload);

            return self::SUCCESS;
        }

        @mkdir(dirname($out), 0o755, true);
        File::put($out, $payload);
        $this->info("Wrote {$out}");

        return self::SUCCESS;
    }
}
