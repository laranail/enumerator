<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Enumerator\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use ReflectionClass;
use ReflectionEnum;
use Simtabi\Laranail\Enumerator\AbstractEnumeratorClass;
use Simtabi\Laranail\Enumerator\Console\Concerns\SupportsNamespacedNames;
use Simtabi\Laranail\Enumerator\Support\IsEnumeratorClass;

/**
 * Emit a PhpStorm / VS Code IDE helper file describing the dynamic
 * methods that PHPStan / IDE inference can't otherwise see — case
 * factories from `HasInvokableCases`, magic `is{Case}()` /
 * `isNot{Case}()` predicates from `HasMagicComparisons`, and the
 * class-const `__callStatic` factory from `HasClassEnumBehavior`.
 *
 * Resolution order for the class list:
 *   1. Positional args (`php artisan enumerator:ide-helper ClassA ClassB`)
 *   2. `config('enumerator.ide_helper.classes')` if set
 *   3. `config('enumerator.cache.auto_warm_classes')` as final fallback
 *
 * The output file is meant to be IDE-indexed, NOT runtime-required.
 * Add it to `.gitignore` and exclude it from composer's autoload to
 * avoid duplicate-class fatals.
 *
 * Emits real method signatures for IDE indexing.
 */
class IdeHelperCommand extends Command
{
    use SupportsNamespacedNames;

    protected $signature = 'laranail::enumerator.ide-helper {classes?* : Specific FQCNs to emit (overrides config)} {--out=_ide_helper_enumerator.php}';

    protected $description = 'Generate IDE helper docblocks for dynamic enumerator methods.';

    /** @var list<string> */
    protected array $commandAliases = ['enumerator:ide-helper'];

    public function handle(): int
    {
        $out = (string) ($this->option('out') ?: '_ide_helper_enumerator.php');

        $classes = $this->resolveClasses();
        if ($classes === []) {
            $this->warn('No enumerator classes found. Pass FQCNs as arguments, or configure enumerator.cache.auto_warm_classes / enumerator.ide_helper.classes.');

            return self::SUCCESS;
        }

        $payload = $this->fileHeader();
        foreach ($classes as $class) {
            $payload .= $this->emitForClass($class);
        }

        File::put(base_path($out), $payload);
        $this->info(sprintf('Wrote %s (%d classes).', $out, count($classes)));

        return self::SUCCESS;
    }

    /**
     * @return array<int, class-string>
     */
    private function resolveClasses(): array
    {
        /** @var array<int, mixed> $args */
        $args = (array) $this->argument('classes');
        $candidates = $args !== []
            ? $args
            : (array) (config('enumerator.ide_helper.classes') ?: config('enumerator.cache.auto_warm_classes', []));

        $resolved = [];
        foreach ($candidates as $candidate) {
            if (is_string($candidate) && IsEnumeratorClass::check($candidate)) {
                /** @var class-string $candidate */
                $resolved[] = $candidate;
            }
        }

        return $resolved;
    }

    private function fileHeader(): string
    {
        return "<?php\n"
            . "\n"
            . "/**\n"
            . " * IDE helper for laranail/enumerator dynamic methods.\n"
            . " *\n"
            . " * This file is NOT meant to be executed. It exists for IDE\n"
            . " * autocomplete + static analysis only. Add to `.gitignore`,\n"
            . " * exclude from composer's autoload, and re-run\n"
            . " * `php artisan enumerator:ide-helper` after each enum change.\n"
            . " */\n"
            . "\n";
    }

    /**
     * @param  class-string  $class
     */
    private function emitForClass(string $class): string
    {
        if (enum_exists($class)) {
            return $this->emitNative($class);
        }

        if (is_subclass_of($class, AbstractEnumeratorClass::class)) {
            /** @var class-string<AbstractEnumeratorClass> $class */
            return $this->emitClassConst($class);
        }

        return '';
    }

    /**
     * @param  class-string  $class
     */
    private function emitNative(string $class): string
    {
        $reflection = new ReflectionEnum($class);
        $namespace = $reflection->getNamespaceName();
        $short = $reflection->getShortName();

        $methods = [];
        // HasInvokableCases: each case is a static factory.
        foreach ($reflection->getCases() as $case) {
            $methods[] = " * @method static {$short} {$case->getName()}()";
        }
        // HasMagicComparisons: per-case predicates.
        foreach ($reflection->getCases() as $case) {
            $methods[] = " * @method bool is{$case->getName()}()";
            $methods[] = " * @method bool isNot{$case->getName()}()";
        }

        return $this->renderClassBlock($namespace, $short, $methods);
    }

    /**
     * @param  class-string<AbstractEnumeratorClass>  $class
     */
    private function emitClassConst(string $class): string
    {
        $reflection = new ReflectionClass($class);
        $namespace = $reflection->getNamespaceName();
        $short = $reflection->getShortName();

        $methods = [];
        foreach ($reflection->getReflectionConstants() as $constant) {
            if (! $constant->isPublic()) {
                continue;
            }
            $name = $constant->getName();
            if (str_starts_with($name, '__')) {
                continue;
            }
            $methods[] = " * @method static {$short} {$name}()";
        }

        return $this->renderClassBlock($namespace, $short, $methods);
    }

    /**
     * @param  list<string>  $methods
     */
    private function renderClassBlock(string $namespace, string $short, array $methods): string
    {
        if ($methods === []) {
            return '';
        }
        $body = implode("\n", $methods);
        $nsHeader = $namespace !== '' ? "namespace {$namespace} {" : 'namespace {';

        return "{$nsHeader}\n    /**\n{$body}\n     */\n    class {$short} {}\n}\n\n";
    }
}
