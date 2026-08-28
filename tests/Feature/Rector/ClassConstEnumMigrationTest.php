<?php

declare(strict_types=1);

use Rector\Rector\AbstractRector;
use Simtabi\Laranail\Enumerator\Rector\Sets\MigrationSet;
use Simtabi\Laranail\Enumerator\Tests\Fixtures\Rector\LegacyBase;
use Simtabi\Laranail\Enumerator\Rector\RectorClassConstEnumToEnumerator;

/**
 * Runs the codemod for real, over a fixture, through the Rector binary.
 *
 * Asserting the rule's shape rather than its output would prove only that it
 * compiles. The interesting failures here are all in what the transformed file
 * says: a `$this->value` left behind is a fatal error the first time that branch
 * runs, and nothing short of running the rule catches it.
 */
/**
 * A disposable project for Rector to work on.
 *
 * Deliberately plain functions rather than `$this->workspace` in a
 * `beforeEach`: Pest's test context is dynamic, so static analysis cannot see
 * those properties and every use is an error it cannot resolve. Passing the
 * path around explicitly costs one variable per test and keeps the suite
 * analysable at level max.
 *
 * @return string the workspace path
 */
function makeRectorWorkspace(?string $config = null): string
{
    if (! class_exists(AbstractRector::class)) {
        test()->markTestSkipped('rector/rector is not installed.');
    }

    $workspace = sys_get_temp_dir() . '/enumerator-rector-' . bin2hex(random_bytes(6));
    mkdir($workspace . '/src', 0o755, true);

    copy(
        __DIR__ . '/../../Fixtures/Rector/legacy-currency.php.inc',
        $workspace . '/src/Currency.php',
    );

    // Nowdoc, not heredoc: the config is full of namespace separators and a
    // heredoc would collapse every one of them.
    file_put_contents($workspace . '/rector.php', $config ?? <<<'PHP'
<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Simtabi\Laranail\Enumerator\Rector\RectorClassConstEnumToEnumerator;

return RectorConfig::configure()
    ->withPaths([__DIR__ . '/src'])
    ->withConfiguredRule(RectorClassConstEnumToEnumerator::class, [
        'Simtabi\Laranail\Enumerator\Tests\Fixtures\Rector\LegacyBase',
    ]);
PHP);

    return $workspace;
}

function runCodemod(string $workspace): string
{
    $binary = dirname(__DIR__, 3) . '/vendor/bin/rector';
    $output = [];
    $exit = 0;

    exec(sprintf(
        '%s process --config=%s --no-progress-bar --no-diffs 2>&1',
        escapeshellarg($binary),
        escapeshellarg($workspace . '/rector.php'),
    ), $output, $exit);

    expect($exit)->toBe(0, "Rector failed:\n" . implode("\n", $output));

    return (string) file_get_contents($workspace . '/src/Currency.php');
}

function dropRectorWorkspace(string $workspace): void
{
    if (is_dir($workspace)) {
        exec('rm -rf ' . escapeshellarg($workspace));
    }
}

it('rebases the class onto AbstractEnumeratorClass', function (): void {
    $workspace = makeRectorWorkspace();
    $result = runCodemod($workspace);
    dropRectorWorkspace($workspace);

    expect($result)
        ->toContain('extends \Simtabi\Laranail\Enumerator\AbstractEnumeratorClass')
        ->not->toContain('extends LegacyBase');
});

it('rewrites $this->value to $this->getValue()', function (): void {
    // The rewrite the whole rule exists for. The property is private on the new
    // base, so a missed one is a fatal error the first time that branch runs —
    // not a type error caught by static analysis.
    $workspace = makeRectorWorkspace();
    $result = runCodemod($workspace);
    dropRectorWorkspace($workspace);

    expect($result)
        ->toContain('match ($this->getValue())')
        ->toContain('$this->getValue() === $other->getValue()')
        ->not->toMatch('/\$this->value\b/');
});

it('leaves the constants alone', function (): void {
    // Not converted to enum cases on purpose: `self::USD` as a string constant
    // is what every match arm compares against. Turning it into an enum case
    // would leave the class compiling and every match falling to its default.
    $workspace = makeRectorWorkspace();
    $result = runCodemod($workspace);
    dropRectorWorkspace($workspace);

    expect($result)
        ->toContain("public const USD = 'USD';")
        ->toContain("public const KES = 'KES';")
        ->not->toContain('case USD');
});

it('renames the factory methods to their enumerator names', function (): void {
    $workspace = makeRectorWorkspace();
    $result = runCodemod($workspace);
    dropRectorWorkspace($workspace);

    expect($result)
        ->toContain('self::fromValue($code)')
        ->toContain('self::tryFromValue($code)')
        ->toContain('static::fromValue($code)')
        ->not->toMatch('/self::from\(/')
        ->not->toMatch('/self::tryFrom\(/')
        ->not->toMatch('/static::of\(/');
});

it('drops $langPath rather than guessing a translation mapping', function (): void {
    // It pointed at a namespace whose keys the rule cannot see. A wrong label is
    // worse than an absent one, and an absent one is visible — label() falls
    // back to humanising the case name.
    $workspace = makeRectorWorkspace();
    $result = runCodemod($workspace);
    dropRectorWorkspace($workspace);

    expect($result)->not->toContain('langPath');
});

it('keeps every domain method', function (): void {
    $workspace = makeRectorWorkspace();
    $result = runCodemod($workspace);
    dropRectorWorkspace($workspace);

    expect($result)
        ->toContain('public function locale(): string')
        ->toContain('public static function pick(')
        ->toContain('public function isSame(');
});

it('produces a file that still parses', function (): void {
    $workspace = makeRectorWorkspace();
    $result = runCodemod($workspace);

    $output = [];
    $exit = 0;
    exec(sprintf('php -l %s 2>&1', escapeshellarg($workspace . '/src/Currency.php')), $output, $exit);

    dropRectorWorkspace($workspace);

    expect($exit)->toBe(0, implode("\n", $output));
    expect($result)->not->toBeEmpty();
});

it('ignores a class extending something else', function (): void {
    $workspace = makeRectorWorkspace();

    file_put_contents($workspace . '/src/Other.php', <<<'PHP'
<?php

namespace Acme\Enums;

class Other extends \ArrayObject
{
    public function read(): mixed
    {
        return $this->value;
    }
}
PHP);

    runCodemod($workspace);
    $other = (string) file_get_contents($workspace . '/src/Other.php');
    dropRectorWorkspace($workspace);

    // A rule that rewrote $this->value everywhere would break unrelated classes
    // that legitimately have a public $value.
    expect($other)
        ->toContain('return $this->value;')
        ->not->toContain('AbstractEnumeratorClass');
});

it('does nothing when no base class is configured', function (): void {
    // Configuring nothing must mean "match nothing", not "match everything".
    $workspace = makeRectorWorkspace(<<<'PHP'
<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Simtabi\Laranail\Enumerator\Rector\RectorClassConstEnumToEnumerator;

return RectorConfig::configure()
    ->withPaths([__DIR__ . '/src'])
    ->withConfiguredRule(RectorClassConstEnumToEnumerator::class, []);
PHP);

    $result = runCodemod($workspace);
    dropRectorWorkspace($workspace);

    expect($result)->toContain('extends LegacyBase');
});

it('is exported by the migration set', function (): void {
    expect(MigrationSet::rules())
        ->toContain(RectorClassConstEnumToEnumerator::class);
});

it('the fixture base is a real class so the fixture reads as valid PHP', function (): void {
    expect(class_exists(LegacyBase::class))->toBeTrue();
});
