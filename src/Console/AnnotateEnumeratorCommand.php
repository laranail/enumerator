<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Console;

use Illuminate\Console\Command;
use ReflectionClass;
use ReflectionEnum;
use Simtabi\Laranail\Enumerator\AbstractEnumeratorClass;
use Simtabi\Laranail\Enumerator\Support\IsEnumeratorClass;

class AnnotateEnumeratorCommand extends Command
{
    protected $signature = 'enumerator:annotate {class? : Fully-qualified enum class. Omit for usage hint.}';

    protected $description = 'Print PHPDoc @method static stubs for case access and meta helpers.';

    public function handle(): int
    {
        $class = (string) ($this->argument('class') ?? '');
        if ($class === '') {
            $this->info('Pass an FQCN, e.g.: php artisan enumerator:annotate "App\\Enums\\UserStatusEnum"');

            return self::SUCCESS;
        }
        // support both native enums and AbstractEnumeratorClass subclasses.
        if (! IsEnumeratorClass::check($class)) {
            $this->error("Class {$class} is not an enumerator (neither a native enum nor an AbstractEnumeratorClass subclass).");

            return self::FAILURE;
        }

        if (enum_exists($class)) {
            $this->annotateNative($class);
        } else {
            /** @var class-string<AbstractEnumeratorClass> $class */
            $this->annotateClassConst($class);
        }

        return self::SUCCESS;
    }

    /**
     * @param  class-string  $class
     */
    private function annotateNative(string $class): void
    {
        $reflection = new ReflectionEnum($class);
        $short = $reflection->getShortName();
        $isBacked = $reflection->isBacked();
        $this->line('/**');
        foreach ($reflection->getCases() as $case) {
            if ($isBacked) {
                $type = (string) $reflection->getBackingType();
                $this->line(" * @method static {$short} {$case->getName()}()  // returns {$type}");
            } else {
                $this->line(" * @method static {$short} {$case->getName()}()");
            }
        }
        $this->line(' */');
    }

    /**
     * @param  class-string<AbstractEnumeratorClass>  $class
     */
    private function annotateClassConst(string $class): void
    {
        $reflection = new ReflectionClass($class);
        $short = $reflection->getShortName();
        $this->line('/**');
        foreach ($reflection->getReflectionConstants() as $constant) {
            if (! $constant->isPublic()) {
                continue;
            }
            $name = $constant->getName();
            if (str_starts_with($name, '__')) {
                continue;
            }
            $value = $constant->getValue();
            $type = is_int($value) ? 'int' : 'string';
            $this->line(" * @method static {$short} {$name}()  // returns {$type}");
        }
        $this->line(' */');
    }
}
