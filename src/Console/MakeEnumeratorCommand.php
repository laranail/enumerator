<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Console;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Facades\File;
use InvalidArgumentException;

class MakeEnumeratorCommand extends GeneratorCommand
{
    protected $signature = 'make:enumerator {name : Class name (e.g. UserStatusEnum)} {--stub=backed : One of: backed|pure|attributes|bitmask|state-machine} {--namespace= : Override the namespace (default App\\Enums)}';

    protected $description = 'Create a new enumerator class.';

    protected $type = 'Enumerator';

    protected function getStub(): string
    {
        $stub = (string) $this->option('stub');
        $allowed = ['backed', 'pure', 'attributes', 'bitmask', 'state-machine'];
        if (! in_array($stub, $allowed, true)) {
            throw new InvalidArgumentException(sprintf('Unknown stub "%s". Allowed: %s', $stub, implode(', ', $allowed)));
        }

        $published = resource_path('stubs/enumerator/enumerator.' . $stub . '.stub');
        if (File::exists($published)) {
            return $published;
        }

        return __DIR__ . '/../../resources/stubs/enumerator.' . $stub . '.stub';
    }

    protected function getDefaultNamespace($rootNamespace): string
    {
        $override = $this->option('namespace');

        return is_string($override) && $override !== '' ? $override : $rootNamespace . '\\Enums';
    }
}
