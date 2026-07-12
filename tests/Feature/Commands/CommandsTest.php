<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Simtabi\Laranail\Enumerator\Presets\Enums\StatusEnum;
use Simtabi\Laranail\Enumerator\Tests\Fixtures\Enums\LegacyStatusEnum;
use Simtabi\Laranail\Enumerator\Tests\Fixtures\Enums\PureColorEnum;

// Console commands — Artisan-runnable behaviour smoke tests.

afterEach(function (): void {
    foreach (glob(sys_get_temp_dir() . '/enum-export-test-*') ?: [] as $f) {
        @unlink($f);
    }
});

// laranail::enumerator.* namespaced names resolve (and old names stay as aliases)

it('resolves the laranail::enumerator.cache primary name', function (): void {
    config()->set('enumerator.cache.auto_warm_classes', [StatusEnum::class]);
    $exit = Artisan::call('laranail::enumerator.cache');
    expect($exit)->toBe(0);
});

it('resolves the laranail::enumerator.cache-clear primary name', function (): void {
    $exit = Artisan::call('laranail::enumerator.cache-clear');
    expect($exit)->toBe(0);
});

it('resolves both the primary name and the legacy alias for annotate', function (): void {
    $viaPrimary = Artisan::call('laranail::enumerator.annotate', ['class' => StatusEnum::class]);
    expect($viaPrimary)->toBe(0);

    $viaAlias = Artisan::call('enumerator:annotate', ['class' => StatusEnum::class]);
    expect($viaAlias)->toBe(0);
});

it('resolves the laranail::enumerator.export primary name', function (): void {
    Artisan::call('laranail::enumerator.export', ['class' => StatusEnum::class]);
    expect(Artisan::output())->toContain('"active"');
});

it('resolves the laranail::enumerator.make primary name', function (): void {
    $exit = Artisan::call('laranail::enumerator.make', ['name' => 'PrimaryNameEnum']);
    expect($exit)->toBe(0);
    expect(file_exists(app()->path('Enums/PrimaryNameEnum.php')))->toBeTrue();
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

it('enumerator:annotate emits @method stubs for backed enums', function (): void {
    Artisan::call('enumerator:annotate', ['class' => StatusEnum::class]);
    $output = Artisan::output();
    expect($output)->toContain('@method static StatusEnum Active()');
    expect($output)->toContain('@method static StatusEnum Archived()');
});

it('enumerator:annotate prints a usage hint when no class is given', function (): void {
    $exit = Artisan::call('enumerator:annotate');
    expect($exit)->toBe(0);
    expect(Artisan::output())->toContain('php artisan enumerator:annotate');
});

it('enumerator:annotate fails for a non-enumerator class', function (): void {
    $exit = Artisan::call('enumerator:annotate', ['class' => stdClass::class]);
    expect($exit)->toBe(1);
});

it('enumerator:annotate handles AbstractEnumeratorClass subclasses', function (): void {
    $exit = Artisan::call('enumerator:annotate', [
        'class' => LegacyStatusEnum::class,
    ]);
    expect($exit)->toBe(0);
    expect(Artisan::output())->toContain('@method static');
});

it('enumerator:annotate emits unannotated @method lines for pure enums', function (): void {
    $exit = Artisan::call('enumerator:annotate', [
        'class' => PureColorEnum::class,
    ]);
    expect($exit)->toBe(0);
    $out = Artisan::output();
    expect($out)->toContain('@method static PureColorEnum Red()');
    expect($out)->not->toContain('returns string');
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

it('enumerator:ide-helper accepts positional class args (overrides config)', function (): void {
    $path = 'enum-export-test-' . bin2hex(random_bytes(4)) . '.php';
    $exit = Artisan::call('enumerator:ide-helper', [
        'classes' => [StatusEnum::class],
        '--out' => $path,
    ]);
    expect($exit)->toBe(0);

    $absolute = app()->basePath($path);
    if (file_exists($absolute)) {
        expect(file_get_contents($absolute))->toContain('namespace');
        @unlink($absolute);
    }
});

it('enumerator:ide-helper emits factory + predicate methods for native enums', function (): void {
    $path = 'enum-export-test-' . bin2hex(random_bytes(4)) . '.php';
    Artisan::call('enumerator:ide-helper', [
        'classes' => [StatusEnum::class],
        '--out' => $path,
    ]);

    $absolute = app()->basePath($path);
    $contents = file_get_contents($absolute);

    expect($contents)->toContain('@method static StatusEnum Active()');
    expect($contents)->toContain('@method bool isActive()');
    expect($contents)->toContain('@method bool isNotActive()');
    expect($contents)->toContain('class StatusEnum {}');

    @unlink($absolute);
});

it('enumerator:ide-helper emits factory stubs for AbstractEnumeratorClass subclasses', function (): void {
    $path = 'enum-export-test-' . bin2hex(random_bytes(4)) . '.php';
    Artisan::call('enumerator:ide-helper', [
        'classes' => [LegacyStatusEnum::class],
        '--out' => $path,
    ]);

    $absolute = app()->basePath($path);
    $contents = file_get_contents($absolute);

    expect($contents)->toContain('@method static LegacyStatusEnum ACTIVE()');
    expect($contents)->toContain('@method static LegacyStatusEnum INACTIVE()');
    expect($contents)->toContain('class LegacyStatusEnum {}');

    @unlink($absolute);
});

it('enumerator:ide-helper warns when no classes are configured', function (): void {
    config()->set('enumerator.ide_helper.classes', null);
    config()->set('enumerator.cache.auto_warm_classes', null);

    $path = 'enum-export-test-' . bin2hex(random_bytes(4)) . '.php';
    $exit = Artisan::call('enumerator:ide-helper', ['--out' => $path]);
    expect($exit)->toBe(0);
    expect(Artisan::output())->toContain('No enumerator classes found');
});

it('enumerator:ide-helper falls back to cache.auto_warm_classes', function (): void {
    config()->set('enumerator.ide_helper.classes', null);
    config()->set('enumerator.cache.auto_warm_classes', [StatusEnum::class]);

    $path = 'enum-export-test-' . bin2hex(random_bytes(4)) . '.php';
    Artisan::call('enumerator:ide-helper', ['--out' => $path]);

    $absolute = app()->basePath($path);
    expect(file_exists($absolute))->toBeTrue();
    expect(file_get_contents($absolute))->toContain('StatusEnum');

    @unlink($absolute);
});

it('enumerator:ide-helper skips non-enumerator candidates silently', function (): void {
    $path = 'enum-export-test-' . bin2hex(random_bytes(4)) . '.php';
    Artisan::call('enumerator:ide-helper', [
        'classes' => [stdClass::class, StatusEnum::class],
        '--out' => $path,
    ]);

    $absolute = app()->basePath($path);
    $contents = file_get_contents($absolute);

    expect($contents)->toContain('StatusEnum');
    expect($contents)->not->toContain('stdClass');

    @unlink($absolute);
});

// make:enumerator

afterEach(function (): void {
    $appDir = app()->path('Enums');
    if (is_dir($appDir)) {
        foreach (glob($appDir . '/*.php') ?: [] as $f) {
            @unlink($f);
        }
        @rmdir($appDir);
    }
});

it('make:enumerator scaffolds a backed-enum class', function (): void {
    $exit = Artisan::call('make:enumerator', ['name' => 'TestBackedEnum']);
    expect($exit)->toBe(0);

    $expected = app()->path('Enums/TestBackedEnum.php');
    expect(file_exists($expected))->toBeTrue();
    expect(file_get_contents($expected))->toContain('enum TestBackedEnum');
});

it('make:enumerator supports the pure stub', function (): void {
    Artisan::call('make:enumerator', ['name' => 'TestPureEnum', '--stub' => 'pure']);
    $expected = app()->path('Enums/TestPureEnum.php');
    expect(file_exists($expected))->toBeTrue();
});

it('make:enumerator supports the attributes stub', function (): void {
    Artisan::call('make:enumerator', ['name' => 'TestAttrsEnum', '--stub' => 'attributes']);
    expect(file_exists(app()->path('Enums/TestAttrsEnum.php')))->toBeTrue();
});

it('make:enumerator supports the bitmask stub', function (): void {
    Artisan::call('make:enumerator', ['name' => 'TestBitmaskEnum', '--stub' => 'bitmask']);
    expect(file_exists(app()->path('Enums/TestBitmaskEnum.php')))->toBeTrue();
});

it('make:enumerator supports the state-machine stub', function (): void {
    Artisan::call('make:enumerator', ['name' => 'TestStateEnum', '--stub' => 'state-machine']);
    expect(file_exists(app()->path('Enums/TestStateEnum.php')))->toBeTrue();
});

it('make:enumerator rejects an unknown stub', function (): void {
    Artisan::call('make:enumerator', ['name' => 'TestUnknownEnum', '--stub' => 'no-such-stub']);
})->throws(InvalidArgumentException::class);
